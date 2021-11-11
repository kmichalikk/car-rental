<script>
	import Router from "svelte-spa-router";
	import Header from "./components/Header.svelte";
	import Main from "./components/Main.svelte";
	import NotFound from "./components/NotFound.svelte";
	import Login from "./components/Login.svelte";
	import Register from "./components/Register.svelte";

	const routes = {
		"/": Main,
		"/login": Login,
		"/register": Register,
		"*": NotFound,
	};
</script>

<Router {routes} />
<Header />

<style global lang="postcss">
	@tailwind base;
	@tailwind components;
	@tailwind utilities;
	:global(body) {
		@apply bg-purple-700;
	}
</style>
