<template>
    <ion-page>
        <ion-header>
            <ion-toolbar>
                <ion-title>Odaberi klub</ion-title>
                <ion-buttons slot="end">
                    <ion-button @click="close">
                        <ion-icon :icon="closeOutline"></ion-icon>
                    </ion-button>
                </ion-buttons>
            </ion-toolbar>
        </ion-header>
        <ion-content>
            <ion-searchbar placeholder="Traži klubove..." @ionChange="onClubSearch"></ion-searchbar>
            <ion-list v-if="clubs.length">
                <ion-item v-for="club in clubs" :key="club.id" :color="club.id == selected.value ? 'primary' : ''" button @click="chooseClub(club.id)" detail="false">
                    <ion-label>{{ club.name }}</ion-label>
                </ion-item>
            </ion-list>
        </ion-content>
    </ion-page>
</template>

<script>
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonContent, IonSearchbar, IonList, IonItem, IonLabel, modalController } from '@ionic/vue';
import { closeOutline } from 'ionicons/icons';
import { ref, computed } from 'vue';
import store from '@/store';

export default {
    name: 'ClubModal',
    components: { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonIcon, IonContent, IonSearchbar, IonList, IonItem, IonLabel },
    props: ['selected', 'change'],
    setup(props) {
        const close = async () => await (await modalController.getTop()).dismiss();

        // Učitaj klubove ako nisu prisutni u storeu.
        const storeClubs = computed(() => store.getters['clubs/clubs']);
        if(!storeClubs.value) {
            store.dispatch('clubs/fetch');
        }

        const clubSearchKeyword = ref();
        const onClubSearch = (event) => clubSearchKeyword.value = event.detail.value || '';

        // Filtriraj klubove.
        const clubs = computed(() => {
            let clubs = storeClubs.value || [];

            if(clubSearchKeyword.value) {
                clubs = clubs.filter(club => {
                    return clubSearchKeyword.value.toLowerCase().split(' ').every(v => club.name.toLowerCase().includes(v));
                })
            }

            return clubs;
        });

        const chooseClub = (club) => {
            props.change(club);
            close();
        }

        return {
            clubs,
            onClubSearch,
            chooseClub,
            close,
            closeOutline
        }
    }
}
</script>