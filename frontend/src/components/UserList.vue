<template>
    <Loading v-if="loading" />
    <transition name="slide-fade">
        <ion-grid v-if="!loading" class="list">
            <ion-row>
                <ion-col v-for="user in users" :key="user.id" size="12" size-md="6" size-lg="4" size-xl="4">
                    <ion-card @click="view(user.id)">
                        <div style="display: flex;">
                            <img v-if="user.logo" :src="competitionLogoUrl(user.logo)" style="max-width: 100px; aspect-ratio: 1; object-fit: cover;">
                            <div style="flex: 1">
                                <ion-card-header>
                                    <ion-card-title>{{ user.name }}</ion-card-title>
                                    <ion-card-subtitle>{{ user.club.name }}</ion-card-subtitle>
                                    <ion-card-subtitle>{{ getCountryName(user.country) }}</ion-card-subtitle>
                                </ion-card-header>
                            </div>
                        </div>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>
    </transition>
</template>

<script>
import { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle } from '@ionic/vue';
import { peopleOutline, locationOutline } from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { getCountryName } from '@/helpers/misc';
import { competitionLogoUrl } from '@/helpers/competition';
import Loading from '@/components/Loading';

export default {
    name: 'UserList',
    components: { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle, Loading },
    props: ['users', 'loading'],
    setup() {
        const router = useRouter();

        // Otvori korisnika.
        const view = (id) => router.push({ name: 'view-user', params: { id }});

        return {
            view,
            getCountryName,
            competitionLogoUrl,
            peopleOutline,
            locationOutline
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
    margin-bottom: 8px;
}

ion-card-subtitle {
    text-transform: initial;
    display: block;
}
</style>