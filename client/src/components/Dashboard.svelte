<script>
	import { push } from "svelte-spa-router";
	import { user } from "../stores";
	import Button from "./Button.svelte";

	let loggingOut = false;
</script>

<main class="pt-24 pb-8 px-6 h-full flex justify-center">
	<div class="rounded-lg shadow-lg h-full px-4 w-full lg:w-4/5 xl:w-3/4 bg-white flex justify-center">
		{#if $user.loggedIn}
			<div class="w-full h-16 flex items-center justify-between">
				<span class="text-3xl">Dashboard</span>
				<Button
					clickFn={() => {
						loggingOut = true;
						let fd = new FormData();
						fd.append("target", "logout");
						fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
							.then((res) => res.json())
							.then((data) => {
								loggingOut = false;
								if (data.ok) {
									user.set({ loggedIn: false, nick: "" });
									push("/");
								}
							});
					}}
					text="Wyloguj"
				/>
			</div>
		{:else}
			<div class="flex justify-center items-center flex-col h-2/3">
				<i class="fas fa-exclamation-triangle fa-10x text-red-500" />
				<span class="mt-12 text-xl">Nie masz dostępu do tego panelu jako niezalogowany użytkownik</span>
			</div>
		{/if}
	</div>
</main>
