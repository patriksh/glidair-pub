import http from '@/http';

export const getUsers = async () => {
    return await http.get('user') || [];
}

export const getUser = async (id) => {
    return await http.get(`user/${id}`);
}

export const createUser = async (payload) => {
    return await http.post('user', payload);
}

export const updateUser = async (id, payload) => {
    payload.append('_method', 'put');
    return await http.post(`user/${id}`, payload);
}

export const deleteUser = async (id) => {
    return await http.post(`user/${id}`, { _method: 'delete' });
}