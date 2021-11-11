<script>
	import { user } from "../stores";
	import { push } from "svelte-spa-router";
	import Button from "./Button.svelte";
	import Logo from "./Logo.svelte";
</script>

<header class="absolute top-0 min-w-full">
	<div class="my-6 mx-6 rounded-lg shadow-lg h-14 px-4 bg-white flex items-center justify-between">
		<div class="flex items-center cursor-pointer" on:click={() => push("/")}>
			<Logo /><span class="text-lg text-purple-700 font-bold pl-8 hidden sm:inline">Rent'n'Drive</span>
		</div>
		{#if $user.loggedIn}
			<Button
				clickFn={() => {
					push("/dashboard");
				}}
				text={$user.nick}
			/>
		{:else}
			<div>
				<Button
					clickFn={() => {
						push("/login");
					}}
					text="Zaloguj się"
				/>
				<span class="hidden sm:inline">lub</span>
				<Button
					clickFn={() => {
						push("/register");
					}}
					text="Zarejestruj się"
					class="hidden sm:inline-block"
				/>
			</div>
		{/if}
	</div>
</header>
