<script>
	import Card from "./Card.svelte";
	import Loading from "./Loading.svelte";
	// obsługa listy samochodów
	let fetchCarsProm = new Promise((resolve, reject) => {
		let fd = new FormData();
		fd.append("target", "getcars");
		fetch("http://localhost:8080/carRental/server/server.php", {
			method: "post",
			body: fd,
		})
			.then((res) => res.json())
			.then((data) => {
				resolve(data.data);
			})
			.catch((err) => reject(err));
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
		{#await fetchCarsProm}
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
						url={item.url}
						make={item.make}
						model={item.model}
						color={item.color}
						body={item.body}
						drive={item.drive}
						power={item.power}
					/>
				{/each}
			</div>
		{:catch err}
			<div class="h-2/3 flex items-center justify-center flex-col">
				<Loading />
				<span class="text-white">Wystąpił błąd podczas pobierania danych:<br /></span>
				<span class="text-white">{err}</span>
			</div>
		{/await}
	</div>
</main>

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
