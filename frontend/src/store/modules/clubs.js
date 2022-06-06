import { Storage } from '@capacitor/storage';
import { getClubs } from '@/http/club';
import { isOnline, offlineToast } from '@/helpers/network';

export default ({
    namespaced: true,
    state: {
        clubs: null
    },
    mutations: {
        // Postavi klubove.
        SET_CLUBS(state, clubs) {
            state.clubs = clubs;
        },
    },
    getters: {
        clubs: state => state.clubs
    },
    actions: {
        // Učitaj klubove sa servera, prikaži poruku ako nema internetske veze.
        async fetch({ state, commit }, toast = false) {
            if(!await isOnline()) {
                if(toast) offlineToast();
                
                if(!state.clubs) {
                    const { value } = await Storage.get({ key: 'clubs' });

                    try {
                        const clubs = JSON.parse(value);
                        commit('SET_CLUBS', clubs);
                    } catch(e) { // eslint-disable-next-line no-empty
                    }
                }
            } else {
                const clubs = await getClubs();
                commit('SET_CLUBS', clubs);
                Storage.set({ key: 'clubs', value: JSON.stringify(clubs) });
            }
        }
    }
});