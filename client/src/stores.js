import {writable} from 'svelte/store';

export const user = writable({loggedIn: false, nick: "", type: ""});
export const filters = writable({numFilters: 0, colors: [], drives: [], makes: [], bodies: []});