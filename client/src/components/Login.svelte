<script>
	import { push, link } from "svelte-spa-router";
	import { user } from "../stores";
	import { SERVER_URL } from "../config";

	let nick = "";
	$: nickValid = nick.length > 0;
	let pass = "";
	$: passValid = pass.length > 0;
	let loginMsg = "";
	let login = (e) => {
		if (nickValid && passValid) {
			let fd = new FormData();
			fd.append("target", "login");
			fd.append("nick", nick);
			fd.append("password", pass);
			fetch(SERVER_URL, { method: "post", body: fd })
				.then((res) => res.json())
				.then((data) => {
					if (data.ok) {
						user.set({ loggedIn: true, nick: nick, type: data.type });
						push("/");
					} else {
						switch (data.msg) {
							case "wrongpass":
								loginMsg = "Niepoprawne hasło";
								break;
							case "blocked":
								loginMsg = "Konto zablokowane";
								break;
							default:
								loginMsg = "Wystąpił nieznany błąd";
								break;
						}
					}
				});
		}
	};
</script>

<div class="w-screen h-screen flex items-center justify-center pb-32">
	<div class="sm:w-2/3 md:w-132 h-96 shadow-lg rounded-lg bg-white flex flex-col items-center p-4 xs:p-12">
		{#if !$user.loggedIn}
			<form on:submit|preventDefault={login} class="flex flex-col h-full items-center justify-evenly pb-2 relative">
				<h1 class="text-purple-700 text-4xl">Zaloguj się</h1>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input type="text" class="bg-purple-100 outline-none w-40" placeholder="Nick" bind:value={nick} />
					{#if !nickValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input type="password" class="bg-purple-100 outline-none w-40" placeholder="Hasło" bind:value={pass} />
					{#if !passValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<input
					type="submit"
					value="Loguj"
					class="bg-purple-700 text-white py-2 px-4 rounded-lg shadow-sm cursor-pointer"
				/>
				<span class="text-red-500 absolute bottom-0">{loginMsg}</span>
			</form>
			<span
				>Nie masz jeszcze konta? <a href="/register" use:link class="text-purple-700 font-semibold">Załóż je</a></span
			>
		{:else}
			<h1 class="text-purple-700 text-2xl">{$user.nick}</h1>
			jesteś już zalogowany
		{/if}
	</div>
</div>
