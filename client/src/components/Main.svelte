<script>
	import { prevent_default } from "svelte/internal";
	import Card from "./Card.svelte";
	let carData = [
		[1, 1, 1, 1, 1, 1, 1, 1],
		[1, 1, 1, 1, 1, 1, 1, 1],
		[1, 1, 1, 1, 1, 1, 1, 1],
		[1, 1, 1, 1, 1, 1, 1, 1],
		[1, 1, 1, 1, 1, 1],
	];
	// scrolling
	let pageHeight;
	let currScroll = 0;
	let scrollTarget = 0;
	let maxScrollTarget = 0;
	let minScrollTarget;
	$: minScrollTarget = -carData.length * pageHeight + pageHeight;
	let scrollPage = (e) => {
		if (e.deltaY < 0 && scrollTarget < maxScrollTarget) {
			scrollTarget = scrollTarget + pageHeight;
		} else if (e.deltaY > 0 && scrollTarget > minScrollTarget) {
			scrollTarget = scrollTarget - pageHeight;
		}
	};
	let prevTime = performance.now();
	let scrollSmoothly = (currTime) => {
		let delta = currTime - prevTime;
		prevTime = currTime;
		if (currScroll != scrollTarget) {
			currScroll = Math.round(
				currScroll + (scrollTarget - currScroll) * 0.8 * delta * 0.01
			);
		}
		window.requestAnimationFrame(scrollSmoothly);
	};
	window.requestAnimationFrame(scrollSmoothly);
</script>

<main
	class="bg-purple-700 w-screen h-screen py-28 flex flex-column flex-wrap justify-center overflow-visible"
>
	<div class="fixed" on:wheel={scrollPage}>
		<div style="transform: translate(0, {currScroll}px">
			{#each carData as page}
				<div class="page-4-2 h-screen" bind:clientHeight={pageHeight}>
					{#each page as elem}
						<Card />
					{/each}
				</div>
			{/each}
		</div>
	</div>
</main>

<style lang="postcss">
	@layer utilities {
		.page-4-2 {
			display: grid;
			grid-template-columns: repeat(4, 272px);
			grid-template-rows: repeat(2, 336px);
		}
	}
</style>
