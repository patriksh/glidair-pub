import http from '@/http';

export const getCompetitions = async (params) => {
    return await http.get('competition', params) || [];
}

export const getCompetition = async (id) => {
    return await http.get(`competition/${id}`);
}

export const createCompetition = async (payload) => {
    return await http.post('competition', payload);
}

export const updateCompetition = async (id, payload) => {
    payload.append('_method', 'put');
    return await http.post(`competition/${id}`, payload);
}

export const updateCompetitionJudges = async (id, payload) => {
    return await http.post(`competition/judges/${id}`, { ...payload, _method: 'put' });
}

export const updateCompetitionParticipants = async (id, payload) => {
    return await http.post(`competition/participants/${id}`, { ...payload, _method: 'put' });
}

export const updateCompetitionParticipantsFromXls = async (id, payload) => {
    payload.append('_method', 'put');
    return await http.post(`competition/participants/xls/${id}`, payload);
}

export const updateCompetitionRounds = async (id, payload) => {
    return await http.post(`competition/rounds/${id}`, { ...payload, _method: 'put' });
}

export const downloadCompetitionReportURL = (id) => {
    return `${process.env.VUE_APP_URL}/api/competition/report/${id}`;
}

export const deleteCompetition = async (id) => {
    return await http.post(`competition/${id}`, { _method: 'delete' });
}