import { Storage } from '@capacitor/storage';
import { getLeaderboardParticipants, getLeaderboardClubs } from '@/http/leaderboard';
import { isOnline, offlineToast } from '@/helpers/network';

export default ({
    namespaced: true,
    state: {
        participants: {},
        clubs: {}
    },
    mutations: {
        // Postavi natjecatelje.
        SET_LEADERBOARD_PARTICIPANTS(state, { participants, year }) {
            state.participants[year] = participants;
        },
        // Postavi klubove.
        SET_LEADERBOARD_CLUBS(state, { clubs, year }) {
            state.clubs[year] = clubs;
        },
    },
    getters: {
        participants: state => state.participants,
        clubs: state => state.clubs
    },
    actions: {
        // Učitaj rezultate natjecatelja, prikaži poruku ako nema internetske veze.
        async fetchParticipants({ state, commit }, { year = null, toast = false }) {
            year = year || 2021; // new Date.getFullYear();

            if(!await isOnline()) {
                if(toast) offlineToast();
                
                if(!state.participants) {
                    const { value } = await Storage.get({ key: 'leaderboard_participants' });

                    try {
                        const participants = JSON.parse(value);
                        commit('SET_LEADERBOARD_PARTICIPANTS', { participants, year });
                    } catch(e) { // eslint-disable-next-line no-empty
                    }
                }
            } else {
                const participants = await getLeaderboardParticipants(year);
                commit('SET_LEADERBOARD_PARTICIPANTS', { participants, year });
                Storage.set({ key: 'leaderboard_participants', value: JSON.stringify(state.participants) });
            }
        },
        // Učitaj rezultate klubova, prikaži poruku ako nema internetske veze.
        async fetchClubs({ state, commit }, { year = null, toast = false }) {
            year = year || 2021; // new Date.getFullYear();

            if(!await isOnline()) {
                if(toast) offlineToast();
                
                if(!state.clubs) {
                    const { value } = await Storage.get({ key: 'leaderboard_clubs' });

                    try {
                        const clubs = JSON.parse(value);
                        commit('SET_LEADERBOARD_CLUBS', { clubs, year });
                    } catch(e) { // eslint-disable-next-line no-empty
                    }
                }
            } else {
                const clubs = await getLeaderboardClubs(year);
                commit('SET_LEADERBOARD_CLUBS', { clubs, year });
                Storage.set({ key: 'leaderboard_clubs', value: JSON.stringify(state.clubs) });
            }
        }
    }
});