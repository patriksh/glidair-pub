<template>
    <div class="filters">
        <ion-chip v-if="yearFilter" color="primary">
            <ion-icon :icon="calendarOutline"></ion-icon>
            <ion-label @click="openYearPicker">{{ selectedYear }}.</ion-label>
        </ion-chip>

        <ion-chip v-if="showCountryChip" color="primary">
            <ion-label>{{ getCountryName(getFilter('country')) }}</ion-label>
            <ion-icon :icon="closeCircle" @click="setFilter('country', '')"></ion-icon>
        </ion-chip>
        <ion-chip v-if="clubFilter && showClubChip" color="primary">
            <ion-label>{{ clubName }}</ion-label>
            <ion-icon :icon="closeCircle" @click="setFilter('club', '')"></ion-icon>
        </ion-chip>

        <ion-chip :color="getFilter('country') == 'HR' ? 'primary': ''">
            <ion-label @click="setFilter('country', 'HR')">Hrvatska</ion-label>
            <ion-icon :icon="closeCircle" v-if="getFilter('country') == 'HR'" @click="setFilter('country', '')"></ion-icon>
        </ion-chip>
        <ion-chip :color="getFilter('country') == 'SI' ? 'primary': ''">
            <ion-label @click="setFilter('country', 'SI')">Slovenija</ion-label>
            <ion-icon :icon="closeCircle" v-if="getFilter('country') == 'SI'" @click="setFilter('country', '')"></ion-icon>
        </ion-chip>
        <ion-chip :color="getFilter('gender') == 'M' ? 'primary': ''">
            <ion-label @click="setFilter('gender', 'M')">Muškarci</ion-label>
            <ion-icon :icon="closeCircle" v-if="getFilter('gender') == 'M'" @click="setFilter('gender', '')"></ion-icon>
        </ion-chip>
        <ion-chip :color="getFilter('gender') == 'F' ? 'primary': ''">
            <ion-label @click="setFilter('gender', 'F')">Žene</ion-label>
            <ion-icon :icon="closeCircle" v-if="getFilter('gender') == 'F'" @click="setFilter('gender', '')"></ion-icon>
        </ion-chip>

        <ion-chip>
            <ion-label @click="openCountryModal">Države</ion-label>
        </ion-chip>

        <ion-chip v-if="clubFilter">
            <ion-label @click="openClubModal">Klubovi</ion-label>
        </ion-chip>
    </div>
</template>

<script>
import { IonChip, IonLabel, IonIcon, pickerController, modalController } from '@ionic/vue';
import { calendarOutline, closeCircle } from 'ionicons/icons';
import { reactive, ref, computed } from 'vue';
import { isDesktop, getCountryName } from '@/helpers/misc';
import store from '@/store';
import CountryModal from '@/components/CountryModal';
import ClubModal from '@/components/ClubModal';

export default {
    name: 'ResultFilters',
    components: { IonChip, IonLabel, IonIcon },
    props: ['year-filter', 'club-filter'],
    emits: ['change', 'year-change'],
    setup(_props, { emit }) {
        const filters = reactive({
            'country': '',
            'gender': '',
            'club': ''
        });

        const getFilter = (filter) => filters[filter];
        const setFilter = (filter, value) => {
            filters[filter] = value;
            emit('change', filters);
        }

        const selectedYear = ref(2021); //

        // Prikaži izbornik za sortiranje.
        const openYearPicker = async () => {
            let options = [];
            let selectedIndex = null;
            let index = 0;

            // TODO: Pull min/max year from server.
            for(let year = 2014; year <= 2022; year++) {
                let option = { text: year, value: year };

                if(selectedYear.value == year) selectedIndex = index;
                index++;

                options.push(option);
            }

            const picker = await pickerController.create({
                columns: [{
                    name: 'year',
                    options,
                    selectedIndex
                }],
                buttons: [
                    {
                        text: 'Odustani',
                        role: 'cancel',
                    },
                    {
                        text: 'Potvrdi',
                        handler: (value) => {
                            selectedYear.value = value.year.value;
                            emit('year-change', selectedYear.value);
                        },
                    },
                ],
            });

            await picker.present();
        };

        const openCountryModal = async () => {
            const modal = await modalController.create({
                component: CountryModal,
                initialBreakpoint: !isDesktop() ? 0.35 : undefined,
                breakpoints: !isDesktop() ? [0, 0.35, 1] : undefined,
                componentProps: {
                    selected: computed(() => getFilter('country')),
                    change: (country) => setFilter('country', country)
                }
            });

            return modal.present();
        };

        const showCountryChip = computed(() => !['', 'HR', 'SI'].includes(filters.country));

        const openClubModal = async () => {
            const modal = await modalController.create({
                component: ClubModal,
                initialBreakpoint: !isDesktop() ? 0.35 : undefined,
                breakpoints: !isDesktop() ? [0, 0.35, 1] : undefined,
                componentProps: {
                    selected: computed(() => getFilter('club')),
                    change: (club) => setFilter('club', club)
                }
            });

            return modal.present();
        };

        const showClubChip = computed(() => filters.club != '');
        const storeClubs = computed(() => store.getters['clubs/clubs']);
        const clubName = computed(() => storeClubs.value?.find((club) => club.id == filters.club)?.name);

        return {
            getFilter,
            setFilter,
            selectedYear,
            openYearPicker,
            openCountryModal,
            showCountryChip,
            getCountryName,
            openClubModal,
            showClubChip,
            clubName,
            calendarOutline,
            closeCircle
        }
    }
}
</script>

<style lang="scss" scoped>
.filters {
    padding-bottom: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
}
</style>