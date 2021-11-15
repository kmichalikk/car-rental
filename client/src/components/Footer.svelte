<script>
	import { serverTime } from "../stores";
	$: time = new Date($serverTime).toUTCString();
</script>

<footer class="absolute bottom-1 right-1 text-purple-500 select-none">
	{time}
</footer>
