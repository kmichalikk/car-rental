<script>
	import { filters } from "../stores";

	import Card from "./Card.svelte";
	import Filter from "./Filter.svelte";
	import Loading from "./Loading.svelte";
	// pobranie listy samochodów
	let serverResponded = false;
	let success = true;
	let errorMessage = "";
	let allCars = [];
	let fd = new FormData();
	fd.append("target", "getcars");
	fetch("http://localhost:8080/carRental/server/server.php", {
		method: "post",
		body: fd,
	})
		.then((res) => res.json())
		.then((data) => {
			serverResponded = true;
			success = true;
			allCars = data.data;
		})
		.catch((err) => {
			serverResponded = true;
			success = false;
			errorMessage = err;
		});

	$: filterCarsProm = new Promise((resolve) => {
		if ($filters.numFilters > 0) {
			let filtered = allCars;
			// jakie marki chcemy?
			let acceptedMakes = $filters.makes.filter((val) => val.selected == true);
			// jeśli jest chociaż jeden filtr marki wybrany, filtrujemy
			// w inny wypadku bierzemy wszystkie
			if (acceptedMakes.length > 0)
				filtered = filtered.filter((car) => acceptedMakes.findIndex((val) => val.name == car.make) != -1);
			// dalej analogicznie
			let acceptedColors = $filters.colors.filter((val) => val.selected == true);
			if (acceptedColors.length > 0)
				filtered = filtered.filter((car) => acceptedColors.findIndex((val) => val.name == car.color) != -1);
			//
			let acceptedBodyTypes = $filters.bodies.filter((val) => val.selected == true);
			if (acceptedBodyTypes.length > 0)
				filtered = filtered.filter((car) => acceptedBodyTypes.findIndex((val) => val.name == car.body) != -1);
			//
			let acceptedDrives = $filters.drives.filter((val) => val.selected == true);
			if (acceptedDrives.length > 0)
				filtered = filtered.filter((car) => acceptedDrives.findIndex((val) => val.name == car.drive) != -1);
			resolve(filtered);
		} else {
			resolve(allCars);
		}
	});

	// płynne scrollowanie - ręcznie, bo nie mamy paska scrollowania
	let currScroll = 0;
	let scrollTarget = 0;
	let minScrollTarget = 0;
	let maxScrollTarget = 0;
	let mouseScrollHandler = (e) => {
		let newTarget = scrollTarget - e.deltaY;
		if (newTarget > -maxScrollTarget && newTarget < minScrollTarget) {
			scrollTarget = newTarget;
		}
	};
	let prevTouchRegisterY = 0;
	let touchStartHandler = (e) => {
		prevTouchRegisterY = e.targetTouches[0].clientY;
	};
	let touchMoveHandler = (e) => {
		let newTarget = scrollTarget + (e.targetTouches[0].clientY - prevTouchRegisterY);
		if (newTarget > -maxScrollTarget && newTarget < minScrollTarget) {
			scrollTarget = newTarget;
		}
		prevTouchRegisterY = e.targetTouches[0].clientY;
	};
	let prevTime = performance.now();
	let scrollSmoothly = (currTime) => {
		let delta = currTime - prevTime;
		prevTime = currTime;
		if (currScroll != scrollTarget) {
			currScroll = Math.round(currScroll + (scrollTarget - currScroll) * 0.8 * delta * 0.01);
		}
		window.requestAnimationFrame(scrollSmoothly);
	};
	window.requestAnimationFrame(scrollSmoothly);
</script>

<main class="w-screen h-screen py-28 flex flex-column flex-wrap justify-center overflow-visible">
	<div
		class="fixed h-full"
		on:wheel={mouseScrollHandler}
		on:touchstart|preventDefault={touchStartHandler}
		on:touchmove|preventDefault={touchMoveHandler}
	>
		{#if !serverResponded}
			<div class="h-2/3 flex items-center justify-center">
				<Loading />
			</div>
		{:else if success}
			{#await filterCarsProm}
				<div class="h-2/3 flex items-center justify-center">
					<Loading />
				</div>
			{:then data}
				<div
					class="xl:grid-xl lg:grid-large md:grid-md grid-xs sm:grid-sm"
					style="transform: translate(0, {currScroll}px"
					bind:clientHeight={maxScrollTarget}
				>
					{#each data as item}
						<Card
							id={item.id}
							url={item.url}
							make={item.make}
							model={item.model}
							color={item.color}
							body={item.body}
							drive={item.drive}
							power={item.power}
							price={item.price}
							wantedBy={item.requestCount}
							booked={item.booked}
						/>
					{/each}
				</div>
			{/await}
		{:else}
			<div class="h-2/3 flex items-center justify-center flex-col">
				<Loading />
				<span class="text-white">Wystąpił błąd podczas pobierania danych:<br /></span>
				<span class="text-white">{errorMessage}</span>
			</div>
		{/if}
	</div>
</main>
<Filter />

<style lang="postcss">
	@layer utilities {
		@variants responsive {
			.grid-large {
				display: grid;
				grid-template-columns: repeat(4, 210px);
				grid-auto-rows: 340px;
				column-gap: 10px;
				row-gap: 10px;
			}
			.grid-xl {
				display: grid;
				grid-template-columns: repeat(5, 210px);
				grid-auto-rows: 340px;
				column-gap: 10px;
				row-gap: 10px;
			}
			.grid-md {
				display: grid;
				grid-template-columns: repeat(3, 210px);
				grid-auto-rows: 340px;
				column-gap: 10px;
				row-gap: 10px;
			}
			.grid-xs {
				display: grid;
				grid-template-columns: repeat(1, 80vw);
				grid-auto-rows: 130vw;
				column-gap: 10px;
				row-gap: 10px;
			}
		}
	}
</style>
