<script>
	export let url = "";
	export let make = "";
	export let model = "";
	export let color = "";
	export let body = "";
	export let drive = "";
	export let power = "";
</script>

<div class="w-full h-full rounded-lg shadow-md flex justify-start flex-col bg-white overflow-hidden cursor-pointer">
	<div class="w-full h-2/5 bg-cover bg-no-repeat" style="background-image: url({url})" />
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
	</div>
</div>
