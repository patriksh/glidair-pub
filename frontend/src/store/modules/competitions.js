import { Storage } from '@capacitor/storage';
import { getCompetitions, getCompetition, updateCompetitionRounds } from '@/http/competition';
import { isOnline, offlineToast } from '@/helpers/network';

export default ({
    namespaced: true,
    state: {
        competitions: null,
        pendingResultUpdates: []
    },
    mutations: {
        // Postavi natjecanja.
        SET_COMPETITIONS(state, competitions) {
            state.competitions = competitions;
        },
        SET_PENDING_RESULT_UPDATE(state, data) {
            state.pendingResultUpdates = data;
        },
        ADD_PENDING_RESULT_UPDATE(state, data) {
            state.pendingResultUpdates = state.pendingResultUpdates.filter(update => update.id !== data.id);
            state.pendingResultUpdates.push(data);
        },
        REMOVE_PENDING_RESULT_UPDATE(state, id) {
            state.pendingResultUpdates = state.pendingResultUpdates.filter(update => update.id !== id);
        }
    },
    getters: {
        competitions: state => state.competitions,
        competition: state => id => state.competitions?.find(c => c.id === id),
        pendingResultUpdates: state => state.pendingResultUpdates,
        pendingResultUpdate: state => id => state.pendingResultUpdates.find(update => update.id === id)
    },
    actions: {
        // Učitaj natjecanja sa servera, prikaži poruku ako nema internetske veze.
        async fetch({ state, commit }, toast = false) {
            if(!await isOnline()) {
                if(toast) offlineToast();

                if(!state.competitions) {
                    const { value } = await Storage.get({ key: 'competitions' });

                    try {
                        const competitions = JSON.parse(value);
                        commit('SET_COMPETITIONS', competitions);
                    } catch(e) { // eslint-disable-next-line no-empty
                    }
                }
            } else {
                const competitions = await getCompetitions();
                commit('SET_COMPETITIONS', competitions);
                Storage.set({ key: 'competitions', value: JSON.stringify(competitions) });
            }
        },
        async fetchSingle({ getters }, id) {
            if(!await isOnline()) {
                return getters.competition(id);
            } else {
                return await getCompetition(id);
            }
        },
        async updateRounds({ commit, state }, data) {
            if(!await isOnline()) {
                commit('ADD_PENDING_RESULT_UPDATE', data);
                Storage.set({ key: 'pending', value: JSON.stringify(state.pendingResultUpdates) });
            } else {
                commit('REMOVE_PENDING_RESULT_UPDATE', data.id);
                Storage.set({ key: 'pending', value: JSON.stringify(state.pendingResultUpdates) });
                return await updateCompetitionRounds(data.id, { rounds: data.rounds });
            }
        },
        async loadPendingResultUpdates({ commit }) {
            const { value } = await Storage.get({ key: 'pending' });

            try {
                const data = JSON.parse(value);
                commit('SET_PENDING_RESULT_UPDATE', data || []);
            } catch(e) { // eslint-disable-next-line no-empty
            }
        }
    }
});