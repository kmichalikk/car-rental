<script>
	let classes = "";
	export { classes as class };
</script>

<div class="bg-purple-50 w-full h-12 rounded-md flex items-center px-4 mt-2 hover:bg-purple-100 {classes}">
	<slot />
</div>
