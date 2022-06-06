import http from '@/http';

export const getClubs = async () => {
    return await http.get('club') || [];
}

export const getClub = async (id) => {
    return await http.get(`club/${id}`);
}

export const createClub = async (payload) => {
    return await http.post('club', payload);
}

export const updateClub = async (id, payload) => {
    payload.append('_method', 'put');
    return await http.post(`club/${id}`, payload);
}

export const deleteClub = async (id) => {
    return await http.post(`club/${id}`, { _method: 'delete' });
}