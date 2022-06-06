<template>
    <master-layout title="Korisnici">
        <template #header-end>
            <SearchbarWithSorting @change="onSearch" placeholder="Traži korisnike..." />
        </template>

        <PullToRefresh :refresh="refresh" />

        <ListContainer>
            <UserList :users="users" :loading="loading" />

            <InfiniteScroll :infinite="infinite" />
        </ListContainer>
    </master-layout>
</template>

<script>
import { ref, computed } from 'vue';
import { isDesktop, prepareForInfiniteScroll, nameAndDateSorter } from '@/helpers/misc';
import store from '@/store';
import SearchbarWithSorting from '@/components/SearchbarWithSorting';
import PullToRefresh from '@/components/PullToRefresh';
import ListContainer from '@/components/ListContainer';
import UserList from '@/components/UserList';
import InfiniteScroll from '@/components/InfiniteScroll';

export default {
    name: 'UsersPage',
    components: { SearchbarWithSorting, PullToRefresh, ListContainer, UserList, InfiniteScroll },
    setup() {
        const loading = ref(true);
        const storeUsers = computed(() => store.getters['users/users']);

        // Učitaj korisnike ako nisu prisutni u storeu.
        if(!storeUsers.value) {
            loading.value = true;
            store.dispatch('users/fetch', true).then(() => loading.value = false);
        }

        const perPage = isDesktop() ? 24 : 12;
        const page = ref(0);
        const total = ref(() => storeUsers.value.length);

        // Lokalno filtriraj i sortiraj korisnike.
        const searchParams = ref({});
        const onSearch = (params) => {
            searchParams.value = params;
            page.value = 0;
        }

        const users = computed(() => {
            let users = storeUsers.value || [];

            if(searchParams.value.keyword) {
                users = storeUsers.value.filter(user => {
                    return searchParams.value.keyword.toLowerCase().split(' ').every(v => user.name.toLowerCase().includes(v) || user.country.toLowerCase().includes(v) || user.club.name.toLowerCase().includes(v));
                })
            }

            users = users.sort(nameAndDateSorter(searchParams.value));

            // Podijeli listu korisnika na dijelove za infinite-scroll.
            return prepareForInfiniteScroll(users, page.value, perPage);
        });

        // Učitaj jos korisnika tijekom scrollanja.
        const infinite = (event) => {
            page.value++;
            event.target.complete();

            if(users.value.length >= total.value) {
                event.target.disabled = true;
            }
        }

        // Osvježi korisnike.
        const refresh = async (event) => store.dispatch('users/fetch', true).then(() => event.target.complete());

        return {
            onSearch,
            refresh,
            users,
            infinite,
            loading
        }
    }
}
</script>