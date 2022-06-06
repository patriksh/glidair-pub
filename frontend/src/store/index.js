import { createStore } from 'vuex';
import AuthModule from './modules/auth';
import CompetitionsModule from './modules/competitions';
import UsersModule from './modules/users';
import ClubsModule from './modules/clubs';
import LeaderboardModule from './modules/leaderboard';
 
const store = createStore({
    modules: {
        auth: AuthModule,
        competitions: CompetitionsModule,
        users: UsersModule,
        clubs: ClubsModule,
        leaderboard: LeaderboardModule
    }
});
 
export default store;