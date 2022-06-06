import http from '@/http';

export const login = async (payload) => {
    return await http.post('login', payload);
}