<template>
    <master-layout title="Natjecanja">
        <template #header-end>
            <SearchbarWithSorting @change="onSearch" placeholder="Traži natjecanja..." />
        </template>

        <PullToRefresh :refresh="refresh" />

        <ListContainer>
            <CompetitionList :competitions="competitions" :loading="loading"></CompetitionList>

            <InfiniteScroll :infinite="infinite" />
        </ListContainer>
    </master-layout>
</template>

<script>
import { ref, computed } from 'vue';
import { isDesktop, nameAndDateSorter, prepareForInfiniteScroll } from '@/helpers/misc';
import store from '@/store';
import SearchbarWithSorting from '@/components/SearchbarWithSorting';
import PullToRefresh from '@/components/PullToRefresh';
import ListContainer from '@/components/ListContainer';
import CompetitionList from '@/components/CompetitionList';
import InfiniteScroll from '@/components/InfiniteScroll';

export default {
    name: 'CompetitionsPage',
    components: { SearchbarWithSorting, PullToRefresh, ListContainer, CompetitionList, InfiniteScroll },
    setup() {
        const loading = ref(true);
        const storeCompetitions = computed(() => store.getters['competitions/competitions']);

        // Učitaj natjecanja ako nisu prisutna u storeu.
        if(!storeCompetitions.value) {
            loading.value = true;
            store.dispatch('competitions/fetch', true).then(() => loading.value = false);
        }

        const perPage = isDesktop() ? 24 : 12;
        const page = ref(0);
        const total = ref(() => storeCompetitions.value.length);

        // Filtriraj natjecanja (sa servera).
        const searchParams = ref({});
        const onSearch = (params) => {
            searchParams.value = params;
            page.value = 0;
        }

        // Podijeli listu natjecanja na dijelove za infinite-scroll.
        const competitions = computed(() => {
            let competitions = storeCompetitions.value || [];

            if(searchParams.value.keyword) {
                competitions = competitions.filter(competition => {
                    return searchParams.value.keyword.toLowerCase().split(' ').every(v => competition.name.toLowerCase().includes(v) || competition.location.toLowerCase().includes(v));
                })
            }

            competitions = competitions.sort(nameAndDateSorter(searchParams.value));

            // Podijeli listu korisnika na dijelove za infinite-scroll.
            return prepareForInfiniteScroll(competitions, page.value, perPage);
        });

        // Učitaj jos natjecanja tijekom scrollanja.
        const infinite = (event) => {
            page.value++;
            event.target.complete();

            if(competitions.value.length >= total.value) {
                event.target.disabled = true;
            }
        }

        // Osvježi natjecanja.
        const refresh = async (event) => store.dispatch('competitions/fetch', true).then(() => event.target.complete());

        return {
            onSearch,
            refresh,
            competitions,
            infinite,
            loading
        }
    }
}
</script>