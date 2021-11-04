<script>
	export let clickFn = () => {};
	let classes = "";
	export { classes as class };
	export let text = "";
</script>

<button on:click={clickFn} class="{classes} rounded-lg w-min-8 h-8 text-white bg-purple-700 px-2">{text}</button>
