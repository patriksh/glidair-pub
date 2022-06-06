import { Storage } from '@capacitor/storage';
import { getUsers } from '@/http/user';
import { isOnline, offlineToast } from '@/helpers/network';

export default ({
    namespaced: true,
    state: {
        users: null
    },
    mutations: {
        // Postavi korisnike.
        SET_USERS(state, users) {
            state.users = users;
        },
    },
    getters: {
        users: state => state.users
    },
    actions: {
        // Učitaj korisnike sa servera, prikaži poruku ako nema internetske veze.
        async fetch({ state, commit }, toast = false) {
            if(!await isOnline()) {
                if(toast) offlineToast();

                if(!state.users) {
                    const { value } = await Storage.get({ key: 'users' });

                    try {
                        const users = JSON.parse(value);
                        commit('SET_USERS', users);
                    } catch(e) { // eslint-disable-next-line no-empty
                    }
                }
            } else {
                const users = await getUsers();
                commit('SET_USERS', users);
                Storage.set({ key: 'users', value: JSON.stringify(users) });
            }
        }
    }
});