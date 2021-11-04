<script>
	import Loading from "./Loading.svelte";
	export let url =
		"http://localhost:8080/carRental/server/images/vw-ogorek.jpg";
</script>

<div
	class="w-full h-full rounded-lg shadow-md flex justify-center bg-white overflow-hidden"
>
	<div
		class="w-full h-2/5 bg-cover bg-no-repeat"
		style="background-image: url({url})"
	/>
</div>
