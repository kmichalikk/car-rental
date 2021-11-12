<script>
	import { user } from "../stores.js";
	import { push } from "svelte-spa-router";
	import Button from "./Button.svelte";
	import Loading from "./Loading.svelte";
	import UserRequestListItem from "./UserRequestListItem.svelte";
	let tabs = ["requests", "users", "deadlines", "simtime"];
	let currTab = "requests";

	//############
	//### requesty
	//############

	// pobranie requestów
	let userRequests = {};
	let updating = true;
	let updateRequests = () => {
		updating = true;
		let fd = new FormData();
		fd.append("target", "getrequests");
		fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				updating = false;
				if (data.ok) {
					userRequests = {};
					for (let req of data.data) {
						if (!userRequests.hasOwnProperty(`${req.carMake} ${req.carModel} (id ${req.carID})`))
							userRequests[`${req.carMake} ${req.carModel} (id ${req.carID})`] = [];
						userRequests[`${req.carMake} ${req.carModel} (id ${req.carID})`].push(req);
					}
				}
			});
	};
	updateRequests();

	let acceptRequest = (reqid, start, end) => {
		console.log(reqid, start, end);
		let fd = new FormData();
		fd.append("target", "acceptrequest");
		fd.append("reqid", reqid);
		fd.append("startdatetime", start.replace("T", " "));
		fd.append("enddatetime", end.replace("T", " "));
		fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) {
					updateRequests();
				}
			});
	};
</script>

<main class="pt-24 pb-8 px-6 h-full flex justify-center">
	<div class="rounded-lg shadow-lg h-full px-4 w-full lg:w-4/5 xl:w-3/4 bg-white flex flex-col overflow-y-hidden">
		<div class="w-full h-16 flex items-center justify-between">
			<span class="text-lg md:text-3xl">Dashboard <span class="hidden sm:inline"> - Panel administratora</span></span>
			<Button
				clickFn={() => {
					let fd = new FormData();
					fd.append("target", "logout");
					fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
						.then((res) => res.json())
						.then((data) => {
							if (data.ok) {
								user.set({ loggedIn: false, nick: "" });
								push("/");
							}
						});
				}}
				text="Wyloguj"
			/>
		</div>
		<div class="flex h-5/6 flex-col overflow-y-hidden">
			<hr />
			<div class="flex my-2 flex-wrap">
				{#each tabs as tab}
					<div
						class="m-1 p-1 md:p-2 w-28 md:w-36 rounded-lg shadow-lg box-border flex items-center justify-center text-lg cursor-pointer flex-wrap
						{currTab == tab ? 'bg-purple-700 text-white' : 'bg-white border-2 border-purple-500 text-purple-700'}"
						on:click={() => {
							currTab = tab;
						}}
					>
						{tab}
					</div>
				{/each}
			</div>
			<hr />
			<div class="p-4 flex flex-col flex-grow h-5/6">
				{#if currTab == "requests"}
					<span class="text-2xl">Prośby o wypożyczenie użytkowników</span>
					<br />
					<span class="text-sm"> System nie przyjmie wypożyczenia zaczynającego się przed aktualną godziną </span>
					{#if !updating}
						<div class="overflow-y-auto my-4">
							{#each Object.keys(userRequests) as key}
								<span class="text-lg">{key}</span>
								<hr />
								{#each userRequests[key] as req}
									<UserRequestListItem
										reqid={req.reqID}
										user={req.userNick}
										prefStart={req.preferredStart.replace(" ", "T")}
										prefEnd={req.preferredEnd.replace(" ", "T")}
										submitFunc={acceptRequest}
									/>
								{/each}
							{/each}
						</div>
					{:else}
						<div class="flex items-center justify-center flex-grow pb-48">
							<Loading color="#6d28d9" />
						</div>
					{/if}
				{:else if currTab == "users"}
					users
				{:else if currTab == "deadlines"}
					deadlines
				{:else if currTab == "simtime"}
					simtime
				{/if}
			</div>
		</div>
	</div>
</main>
