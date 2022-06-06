<template>
    <master-layout title="Klubovi">
        <template #header-end>
            <SearchbarWithSorting @change="onSearch" placeholder="Traži klubove..." />
        </template>

        <PullToRefresh :refresh="refresh" />

        <ListContainer>
            <ClubList :clubs="clubs" :loading="loading" />

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
import ClubList from '@/components/ClubList';
import InfiniteScroll from '@/components/InfiniteScroll';

export default {
    name: 'ClubsPage',
    components: { SearchbarWithSorting, PullToRefresh, ListContainer, ClubList, InfiniteScroll },
    setup() {
        const loading = ref(true);
        const storeClubs = computed(() => store.getters['clubs/clubs']);

        // Učitaj klubove ako nisu prisutni u storeu.
        if(!storeClubs.value) {
            loading.value = true;
            store.dispatch('clubs/fetch', true).then(() => loading.value = false);
        }

        const perPage = isDesktop() ? 48 : 12;
        const page = ref(0);
        const total = ref(() => storeClubs.value.length);

        // Lokalno filtriraj i sortiraj klubove.
        const searchParams = ref({});
        const onSearch = (params) => {
            searchParams.value = params;
            page.value = 0;
        }

        const clubs = computed(() => {
            let clubs = storeClubs.value || [];

            if(searchParams.value.keyword) {
                clubs = storeClubs.value.filter(club => {
                    return searchParams.value.keyword.toLowerCase().split(' ').every(v => club.name.toLowerCase().includes(v));
                })
            }

            clubs = clubs.sort(nameAndDateSorter(searchParams.value));

            // Podijeli listu klubova na dijelove za infinite-scroll.
            return prepareForInfiniteScroll(clubs, page.value, perPage);
        });

        // Učitaj jos klubova tijekom scrollanja.
        const infinite = (event) => {
            page.value++;
            event.target.complete();

            if(clubs.value.length >= total.value) {
                event.target.disabled = true;
            }
        }

        // Osvježi klubove.
        const refresh = async (event) => store.dispatch('clubs/fetch', true).then(() => event.target.complete());

        return {
            onSearch,
            refresh,
            clubs,
            infinite,
            loading
        }
    }
}
</script>