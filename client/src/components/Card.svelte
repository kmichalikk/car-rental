<script>
	import { push } from "svelte-spa-router";

	export let id;
	export let url = "";
	export let make = "";
	export let model = "";
	export let color = "";
	export let body = "";
	export let drive = "";
	export let power = "";
	export let price = 0;
	export let wantedBy = 0;
	export let booked = false;
</script>

<div
	class="w-full h-full rounded-lg shadow-md flex justify-start flex-col bg-white overflow-hidden cursor-pointer relative"
	on:click={() => {
		push("/details/" + id);
	}}
>
	<div class="w-full h-2/5 bg-cover bg-no-repeat" style="background-image: url({url})" />
	{#if wantedBy > 0}
		<div class=" absolute right-2 top-2 h-8 px-2 bg-purple-700 text-white rounded-lg shadow-lg flex items-center">
			{wantedBy} <i class="far fa-eye pl-2" />
		</div>
	{/if}
	<div class="p-4">
		<span class="font-bold text-4xl md:text-xl">{make} {model}</span><br />
		<span class="font-bold text-2xl md:text-base text-purple-700">kolor: </span>
		<span class=" text-2xl md:text-base">{color}</span><br />
		<span class="font-bold text-2xl md:text-base text-purple-700">nadwozie: </span>
		<span class=" text-2xl md:text-base">{body}</span><br />
		<span class="font-bold text-2xl md:text-base text-purple-700">napęd: </span>
		<span class=" text-2xl md:text-base">{drive}</span><br />
		<span class="font-bold text-2xl md:text-base text-purple-700">moc: </span>
		<span class=" text-2xl md:text-base">{power} KM</span><br />
		<span class="font-bold text-2xl md:text-base text-purple-700">cena: </span>
		<span class=" text-2xl md:text-base">{price} zł/h</span><br />
	</div>
	{#if booked}
		<div
			class="absolute w-full h-full left-0 top-0 bg-purple-400 bg-opacity-75 flex items-center justify-center cursor-not-allowed"
			on:click|stopPropagation={() => {}}
		>
			<span class="text-white font-bold text-2xl">zarezerwowany</span>
		</div>
	{/if}
</div>
