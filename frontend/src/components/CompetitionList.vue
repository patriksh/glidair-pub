<template>
    <Loading v-if="loading" height="80%" />
    <transition name="slide-fade">
        <ion-grid v-if="!loading" class="list">
            <ion-row>
                <ion-col v-for="competition in competitions" :key="competition.id" size="12" size-md="6" size-lg="4" size-xl="4">
                    <ion-card @click="view(competition.id)">
                        <img :src="competition.logo ? competitionLogoUrl(competition.logo) : require('@/assets/img/competition.jpg')">
                        <ion-card-header>
                            <ion-card-title>{{ competition.name }}</ion-card-title>
                        </ion-card-header>
                        <ion-card-content>
                            <ion-chip>
                                <ion-icon :icon="locationOutline" color="primary"></ion-icon>
                                <ion-label>{{ competition.location }}</ion-label>
                            </ion-chip>
                            <ion-chip>
                                <ion-icon :icon="calendarOutline" color="primary"></ion-icon>
                                <ion-label>{{ competitionDateFormatted(competition.date) }}</ion-label>
                            </ion-chip>
                        </ion-card-content>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>
    </transition>
</template>

<script>
import { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel } from '@ionic/vue';
import { calendarOutline, locationOutline } from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { competitionLogoUrl, competitionDateFormatted } from '@/helpers/competition';
import Loading from '@/components/Loading';

export default {
    name: 'CompetitionList',
    components: { IonGrid, IonRow, IonCol, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel, Loading },
    props: ['competitions', 'loading'],
    setup() {
        const router = useRouter();

        // Otvori natjecanje.
        const view = (id) => router.push({ name: 'view-competition', params: { id }});

        return {
            view,
            competitionLogoUrl,
            competitionDateFormatted,
            calendarOutline,
            locationOutline
        }
    }
}
</script>

<style lang="scss" scoped>
ion-card {
    height: calc(100% - 16px);
}

img {
    aspect-ratio: 16 / 9;
    object-fit: cover;
}

ion-card-content {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;

    ion-chip {
        margin-inline: 0;
    }
}

ion-card-title {
    font-size: 22px;
}
</style>