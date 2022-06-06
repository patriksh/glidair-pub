<template>
    <Loading v-if="loading" />
    <transition name="slide-fade">
        <ion-grid v-if="!loading" class="list">
            <ion-row>
                <ion-col v-for="club in clubs" :key="club.id" size="12" size-md="6" size-lg="4" size-xl="4">
                    <ion-card @click="view(club.id)">
                        <div style="display: flex;">
                            <img v-if="club.logo" :src="competitionLogoUrl(club.logo)" style="max-width: 100px; aspect-ratio: 1; object-fit: cover;">
                            <ion-card-header style="flex: 1;">
                                <ion-card-title>{{ club.name }}</ion-card-title>
                            </ion-card-header>
                        </div>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>
    </transition>
</template>

<script>
import { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle } from '@ionic/vue';
import { useRouter } from 'vue-router';
import { competitionLogoUrl } from '@/helpers/competition';
import Loading from '@/components/Loading';

export default {
    name: 'ClubList',
    components: { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle, Loading },
    props: ['clubs', 'loading'],
    setup() {
        const router = useRouter();

        // Otvori klub.
        const view = (id) => router.push({ name: 'view-club', params: { id }});

        return {
            view,
            competitionLogoUrl
        }
    }
}
</script>

<style lang="scss" scoped>
ion-card {
    height: calc(100% - 16px);
}

ion-card-title {
    font-size: 22px;
}
</style>