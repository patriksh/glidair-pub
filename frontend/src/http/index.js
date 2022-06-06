import axios from 'axios';
import store from '@/store';
import router from '@/router';

const axiosInstance = axios.create({
    baseURL: `${process.env.VUE_APP_URL}/api/`,
    headers: {
        'Content-type': 'application/json',
    },
});

// Ako je korisnik prijavljen dodaj token u header pri svakom zahtjevu poslanom na server.
axiosInstance.interceptors.request.use((config) => {
    const token = store.getters['auth/token'];
    if(token) config.headers['Authorization'] = `Bearer ${token}`;
    
    return config;
});

// Ako je token nevaljan (http 401) odjavi korisnika.
axiosInstance.interceptors.response.use((response) => response, async (error) => {
    if(error.response.status === 401) {
        store.dispatch('auth/logout');
        router.replace({ name: 'login' });
    }

    return Promise.reject(error);
});

export default {
    raw: axiosInstance,

    // GET request, dodaj parametre u URL ako je potrebno.
    async get(url, params = null) {
        if(params) url += '?' + (new URLSearchParams(params).toString());

        try {
            const { data } = await axiosInstance.get(url);
            return data;
        } catch(error) {
            return false;
        }
    },

    // POST request, u slučaju greške vrati status: 0 zajedno sa porukom.
    async post(url, payload) {
        try {
            const { data } = await axiosInstance.post(url, payload);
            return data;
        } catch(error) {
            let data = { status: 0, message: 'Došlo je do greške.' };
            if(error?.response?.data?.message) data.message = error.response.data.message;

            return data;
        }
    },

    // PUT request, u slučaju greške vrati status: 0 zajedno sa porukom.
    async put(url, payload) {
        try {
            const { data } = await axiosInstance.put(url, payload);
            return data;
        } catch(error) {
            let data = { status: 0, message: 'Došlo je do greške.' };
            if(error?.response?.data?.message) data.message = error.response.data.message;

            return data;
        }
    },

    // DELETE request.
    async delete(url, payload) {
        try {
            await axiosInstance.delete(url, payload);
        } catch(error) {
            return false;
        }
    },
}