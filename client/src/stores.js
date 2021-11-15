import {SERVER_URL} from "./config";
import {get, writable, readable} from 'svelte/store';

export const user = writable({loggedIn: false, nick: "", type: ""});
export const filters = writable({numFilters: 0, colors: [], drives: [], makes: [], bodies: []});

let updTime = (set) => {
		let fd = new FormData();
		fd.append("target", "getservertime");
		fetch(SERVER_URL, {method: "post", body: fd})
		.then(res => res.json())
		.then(data => {
			if (data.ok)
				set(data.time * 1000)	
		})
}

export const serverTimeNeedsUpdate = writable({needsUpdate: false});

export const serverTime = readable(0, (set) => {
	let interval = setInterval(() => {
		updTime(set);
	}, 600000)
	let emergencyInterval = setInterval(() => {
		if (get(serverTimeNeedsUpdate).needsUpdate) {
			serverTimeNeedsUpdate.set({needsUpdate: false})
			updTime(set);
		}
	}, 1000);
	updTime(set);
	return () => {
		clearInterval(interval);
		clearInterval(emergencyInterval);
	}
})