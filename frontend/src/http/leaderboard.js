import http from '@/http';

export const getLeaderboardParticipants = async (year) => {
    return await http.get(`leaderboard/participants?year=${year}`);
}

export const getLeaderboardClubs = async (year) => {
    return await http.get(`leaderboard/clubs?year=${year}`);
}