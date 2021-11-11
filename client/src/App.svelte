<script>
	import { filters, user } from "./stores";
	import Router from "svelte-spa-router";
	import Header from "./components/Header.svelte";
	import Main from "./components/Main.svelte";
	import NotFound from "./components/NotFound.svelte";
	import Login from "./components/Login.svelte";
	import Register from "./components/Register.svelte";
	import Dashboard from "./components/Dashboard.svelte";

	const routes = {
		"/": Main,
		"/login": Login,
		"/register": Register,
		"/dashboard": Dashboard,
		"*": NotFound,
	};
	// próba logowania
	let fd = new FormData();
	fd.append("target", "hello");
	fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
		.then((res) => res.json())
		.then((data) => {
			if (data.ok) user.set({ loggedIn: true, nick: data.nick });
		})
		.catch((err) => console.log(err));
	// próba pobrania filtrów
	let fd2 = new FormData();
	fd.append("target", "getfilters");
	fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
		.then((res) => res.json())
		.then((json) => {
			if (json.ok)
				filters.set({
					numFilters: 0,
					colors: json.data.colors.map((val) => {
						return { ...val, selected: false };
					}),
					makes: json.data.makes.map((val) => {
						return { ...val, selected: false };
					}),
					drives: json.data.drives.map((val) => {
						return { ...val, selected: false };
					}),
					bodies: json.data.bodies.map((val) => {
						return { ...val, selected: false };
					}),
				});
		})
		.catch((err) => console.log(err));
</script>

<Router {routes} />
<Header />

<style global lang="postcss">
	@tailwind base;
	@tailwind components;
	@tailwind utilities;
	:global(body) {
		@apply bg-purple-700 h-full m-0 p-0;
	}
	:global(html) {
		@apply h-full m-0 p-0;
	}
</style>
