<script>
	export let val = false;
	export let clickFn = () => {};
</script>

<div
	class="w-16 h-8 px-1 flex items-center rounded-2xl justify-between cursor-pointer shadow-inner {val
		? 'bg-purple-700 flex-row'
		: 'bg-gray-300 flex-row-reverse'}"
	on:click={clickFn}
>
	<i class="fas text-lg px-1 {val ? 'fa-check text-purple-300' : 'fa-times text-gray-500'}" />
	<div class="w-6 h-6 shadow-sm rounded-xl bg-white " />
</div>
