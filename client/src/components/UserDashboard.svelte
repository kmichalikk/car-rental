<script>
	import { push } from "svelte-spa-router";
	import { user } from "../stores";
	import Button from "./Button.svelte";
	import ListItem from "./ListItem.svelte";
	import QrCode from "svelte-qrcode";
	import Loading from "./Loading.svelte";

	let loggingOut = false;
	let userRequested = [];
	let userBooked = [];
	let fd = new FormData();
	let fetching = true;
	fd.append("target", "mycars");
	fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
		.then((res) => res.json())
		.then((data) => {
			fetching = false;
			if (data.ok) {
				userRequested = data.data.filter((val) => val.requested);
				userBooked = data.data.filter((val) => val.booked);
			}
		});
	let showQrCode = "";
	let qrCodeVal = "";
</script>

<main class="pt-24 pb-8 px-6 h-full flex justify-center">
	<div class="rounded-lg shadow-lg h-full px-4 w-full lg:w-4/5 xl:w-3/4 bg-white flex flex-col justify-center">
		{#if $user.loggedIn && $user.type == "user"}
			<div class="w-full h-16 flex items-center justify-between">
				<span class="text-lg md:text-3xl">Dashboard - Twoje samochody</span>
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
			{#if !fetching}
				<div class="flex-grow px-8 mb-4 overflow-y-auto">
					<span class="text-xl">Oczekujące na realizację</span>
					<hr />
					{#each userRequested as car}
						<ListItem>
							<span class="px-2 w-48"> {car.make} {car.model} </span>
							<span class="px-2 hidden md:block"><b>od:</b> {car.start}</span>
							<span class="px-2 hidden md:block"><b>do:</b> {car.end}</span>
						</ListItem>
					{/each}
					<br />
					<span class="text-xl">Zarezerwowane przez Ciebie</span>
					<hr />
					{#each userBooked as car}
						<ListItem>
							<span class="px-2 w-48"> {car.make} {car.model} </span>
							<span class="px-2 hidden md:block"><b>od:</b> {car.start}</span>
							<span class="px-2 hidden md:block"><b>do:</b> {car.end}</span>
							<div class="flex flex-grow justify-end">
								<i
									class="fas fa-qrcode relative text-2xl hover:text-purple-700 cursor-pointer"
									on:click={() => {
										qrCodeVal = car.make + car.model + car.start + car.end;
										showQrCode = true;
									}}
								/>
							</div>
						</ListItem>
					{/each}
				</div>
			{:else}
				<div class="flex items-center justify-center flex-grow pb-48">
					<Loading color="#6d28d9" />
				</div>
			{/if}
		{:else}
			<div class="flex justify-center items-center flex-col h-2/3">
				<i class="fas fa-exclamation-triangle fa-10x text-red-500" />
				<span class="mt-12 text-xl">Nie masz dostępu do tego panelu</span>
			</div>
		{/if}
	</div>
	<div
		class="{showQrCode
			? 'block'
			: 'hidden'} p-0 pb-48 m-0 h-full w-full shadow-lg fixed l-0 t-0 bg-white bg-opacity-0 flex items-center justify-center"
		on:click={() => {
			showQrCode = false;
		}}
	>
		<div class="h-72 w-72 bg-white bg-opacity-100 rounded-lg shadow-lg flex items-center justify-center">
			<QrCode value={qrCodeVal} size={256} />
		</div>
	</div>
</main>
