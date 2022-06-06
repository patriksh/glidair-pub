import { Storage } from '@capacitor/storage';

export default ({
    namespaced: true,
    state: {
        loggedIn: false,
        checkedStorage: false,
        token: '',
    },
    mutations: {
        // Postavi status prijave i token.
        SET_TOKEN(state, token) {
            state.loggedIn = true;
            state.token = token;
        },
        // Spremi token u web preglednik ili uređaj (ovisno o platformi).
        SAVE_TOKEN(_state, token) {
            Storage.set({ key: 'token', value: token });
        },
        // Izbriši token.
        REMOVE_TOKEN(state) {
            state.loggedIn = false;
            state.token = '';
            Storage.remove({ key: 'token' });
        },
        UPDATE_CHECKED_STORAGE(state, value) {
            state.checkedStorage = value;
        },
    },
    getters: {
        isLoggedIn: state => state.loggedIn,
        token: state => state.token
    },
    actions: {
        // Prijavi se, postavi i spremi token.
        async login({ commit }, token) {
            commit('SET_TOKEN', token);
            commit('SAVE_TOKEN', token);
        },
        // Provjeri (samo jedanput) postoji li token te prijavi korisnika ako postoji.
        async loginFromStorage({ state, commit }) {
            if(state.checkedStorage) return;
            
            commit('UPDATE_CHECKED_STORAGE', true);

            if(state.loggedIn) return;

            const { value } = await Storage.get({ key: 'token' });

            if(!value) return;

            commit('SET_TOKEN', value);
        },
        // Odjavi se, ukloni token.
        async logout({ commit }) {
            commit('REMOVE_TOKEN');
        }
    }
});