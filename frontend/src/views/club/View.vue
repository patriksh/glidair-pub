<template>
    <master-layout :title="pageTitle">
        <ion-grid class="page" fixed>
            <ion-row>
                <ion-col size="12" size-lg="8">
                    <ion-card>
                        <div style="display: flex;">
                            <img v-if="club?.logo" :src="competitionLogoUrl(club.logo)" style="max-width: 100px; aspect-ratio: 1; object-fit: cover;">
                            <ion-card-header style="flex: 1;">
                                <ion-card-title>
                                    <template v-if="club">{{ club.name }}</template>
                                    <ion-skeleton-text v-else animated style="line-height: 25px;"></ion-skeleton-text>
                                </ion-card-title>
                            </ion-card-header>
                        </div>
                    </ion-card>
                    <ClubAdminButtons class="ion-hide-lg-up" :club="club" />
                    <ion-card class="members" v-if="club?.users.length">
                        <ion-card-header>
                            <ion-card-title>Članovi</ion-card-title>
                        </ion-card-header>
                        <ion-card-content>
                            <ion-list>
                                <ion-item v-for="user in club.users" :key="user.id" @click="viewUser(user.id)">
                                    <ion-label>{{ user.name }} ({{ user.country }})</ion-label>
                                </ion-item>
                            </ion-list>
                        </ion-card-content>
                    </ion-card>
                    <ion-card class="members" v-if="!club">
                        <ion-card-header>
                            <ion-card-title><ion-skeleton-text style="width: 85px; line-height: 26.2px;"></ion-skeleton-text></ion-card-title>
                        </ion-card-header>
                    </ion-card>
                </ion-col>
                <ion-col size="12" size-lg="4">
                    <ClubAdminButtons class="ion-hide-lg-down" :club="club" />
                </ion-col>
            </ion-row>
        </ion-grid>

        <template #toolbar-buttons-end v-if="club">
            <ion-button @click="openDeletePopover"><ion-icon :icon="ellipsisVerticalOutline"></ion-icon></ion-button>
        </template>
    </master-layout>
</template>

<script>
import { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonLabel, IonGrid, IonRow, IonCol, IonButton, IonList, IonItem, IonIcon, IonSkeletonText, onIonViewWillEnter, toastController, popoverController } from '@ionic/vue';
import { ellipsisVerticalOutline } from 'ionicons/icons';
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { getClub, deleteClub } from '@/http/club';
import { clubNotFoundOffline } from '@/helpers/club';
import { competitionLogoUrl } from '@/helpers/competition';
import store from '@/store';
import ClubAdminButtons from '@/components/ClubAdminButtons';
import DeletePopover from '@/components/DeletePopover';

export default {
    name: 'ClubViewPage',
    components: { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonLabel, IonGrid, IonRow, IonCol, IonButton, IonList, IonItem, IonIcon, IonSkeletonText, ClubAdminButtons },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const club = ref();
        const id = parseInt(route.params.id);

        // Učitaj podatke kluba sa servera po IDu iz URLa.
        if(isNaN(id)) return clubNotFoundOffline();

        const fetchClub = async () => club.value = await getClub(id) || await clubNotFoundOffline();
        
        onIonViewWillEnter(() => fetchClub());

        const pageTitle = computed(() => club.value?.name ? club.value.name : 'Klub');

        // Izbriši klub, prikaži poruku i otvori popis svih klubova.
        const onDeleteClub = async () => {
            await deleteClub(id);
            store.dispatch('clubs/fetch');

            router.push({ name: 'clubs' });
            toastController.create({ message: 'Klub je uspješno izbrisan.', duration: 2000 }).then(t => t.present());
        }

        const deletePopover = ref();
        const openDeletePopover = async (event) => {
            deletePopover.value = await popoverController.create({
                component: DeletePopover,
                componentProps: {
                    delete: onDeleteClub,
                    text: 'Izbriši klub',
                    message: 'Jeste li sigurni da želite izbrisati ovaj klub?'
                },
                event
            }).then(p => p.present());
        }

        const viewUser = (id) => router.push({ name: 'view-user', params: { id }});

        return {
            club,
            pageTitle,
            openDeletePopover,
            viewUser,
            competitionLogoUrl,
            ellipsisVerticalOutline
        }
    },
}
</script>

<style lang="scss" scoped>
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

.members {
    ion-list {
        background: none;
        width: 100%;
    }

    ion-item {
        --padding-start: 0px;
        --padding-end: 0px;
    }
    
    @media(max-width: 991px) {
        margin-top: 0px;
    }
}
</style>