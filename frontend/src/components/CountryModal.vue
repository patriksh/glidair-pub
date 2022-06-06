<template>
    <ion-page>
        <ion-header>
            <ion-toolbar>
                <ion-title>Odaberi državu</ion-title>
                <ion-buttons slot="end">
                    <ion-button @click="close">
                        <ion-icon :icon="closeOutline"></ion-icon>
                    </ion-button>
                </ion-buttons>
            </ion-toolbar>
        </ion-header>
        <ion-content>
            <ion-searchbar placeholder="Traži države..." @ionChange="onCountrySearch"></ion-searchbar>
            <ion-list>
                <ion-item v-for="country in countries" :key="country.code" :color="country.code == selected.value ? 'primary' : ''" button @click="chooseCountry(country.code)" detail="false">
                    <ion-label>{{ country.name }}</ion-label>
                </ion-item>
            </ion-list>
        </ion-content>
    </ion-page>
</template>

<script>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonContent, IonSearchbar, IonList, IonItem, IonLabel, modalController } from '@ionic/vue';
import { closeOutline } from 'ionicons/icons';
import { ref, computed } from 'vue';
import { getCountries } from '@/helpers/misc';

export default {
    name: 'CountryModal',
    components: { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonContent, IonSearchbar, IonList, IonItem, IonLabel },
    props: ['selected', 'change'],
    setup(props) {
        const close = async () => await (await modalController.getTop()).dismiss();

        // Učitaj popis svih država.
        const dbCountries = getCountries();

        const countrySearchKeyword = ref();
        const onCountrySearch = (event) => countrySearchKeyword.value = event.detail.value || '';

        // Filtriraj države.
        const countries = computed(() => {
            let countries = dbCountries || [];

            if(countrySearchKeyword.value) {
                countries = countries.filter(country => {
                    return countrySearchKeyword.value.toLowerCase().split(' ').every(v => country.name.toLowerCase().includes(v));
                })
            }

            return countries;
        });

        const chooseCountry = (country) => {
            props.change(country);
            close();
        }

        return {
            countries,
            onCountrySearch,
            chooseCountry,
            close,
            closeOutline
        }
    }
}
</script>