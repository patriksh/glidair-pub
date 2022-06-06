<template>
    <master-layout :title="pageTitle">
        <ion-grid class="page" fixed>
            <ion-row>
                <ion-col size="12" size-lg="8">
                    <ion-card>
                        <div style="display: flex;">
                            <template v-if="user">
                                <img v-if="user.logo" :src="competitionLogoUrl(user.logo)" style="max-width: 100px; aspect-ratio: 1; object-fit: cover;">
                            </template>
                            <div v-else style="width: 100px; height: 125px;"><ion-skeleton-text animated></ion-skeleton-text></div>
                            <div style="flex: 1">
                                <ion-card-header>
                                    <ion-card-title>
                                        <template v-if="user">{{ user.name }}</template>
                                        <ion-skeleton-text v-else animated style="line-height: 25px;"></ion-skeleton-text>
                                    </ion-card-title>
                                </ion-card-header>
                                <ion-card-content>
                                    <ion-chip>
                                        <template v-if="user">
                                            <ion-icon :icon="peopleOutline" color="primary"></ion-icon>
                                            <ion-label>{{ user.club.name }}</ion-label>
                                        </template>
                                        <ion-skeleton-text v-else animated style="width: 60px; line-height: 16px; border-radius: 100px;"></ion-skeleton-text>
                                    </ion-chip>
                                    <ion-chip>
                                        <template v-if="user">
                                            <ion-icon :icon="locationOutline" color="primary"></ion-icon>
                                            <ion-label>{{ getCountryName(user.country) }}</ion-label>
                                        </template>
                                        <ion-skeleton-text v-else animated style="width: 60px; line-height: 16px; border-radius: 100px;"></ion-skeleton-text>
                                    </ion-chip>
                                </ion-card-content>
                            </div>
                        </div>
                    </ion-card>
                    <UserAdminButtons class="ion-hide-lg-up" :user="user" />
                    <ion-card class="competitions" v-if="user?.participants.length">
                        <ion-card-header>
                            <ion-card-title>Natjecanja</ion-card-title>
                        </ion-card-header>
                        <ion-card-content>
                            <ion-list>
                                <ion-item v-for="participant in user.participants" :key="participant.id" @click="viewCompetition(participant.competition.id)">
                                    <ion-label>{{ participant.competition.name }}</ion-label>
                                </ion-item>
                            </ion-list>
                        </ion-card-content>
                    </ion-card>
                    <ion-card class="competitions" v-if="!user">
                        <ion-card-header>
                            <ion-card-title><ion-skeleton-text style="width: 115px; line-height: 26.2px;"></ion-skeleton-text></ion-card-title>
                        </ion-card-header>
                    </ion-card>
                </ion-col>
                <ion-col size="12" size-lg="4">
                    <UserAdminButtons class="ion-hide-lg-down" :user="user" />
                </ion-col>
            </ion-row>
        </ion-grid>

        <template #toolbar-buttons-end v-if="user">
            <ion-button @click="openDeletePopover"><ion-icon :icon="ellipsisVerticalOutline"></ion-icon></ion-button>
        </template>
    </master-layout>
</template>

<script>
import { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel, IonGrid, IonRow, IonCol, IonButton, IonList, IonItem, IonSkeletonText, onIonViewWillEnter, toastController, popoverController } from '@ionic/vue';
import { peopleOutline, locationOutline, ellipsisVerticalOutline } from 'ionicons/icons';
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router';
import { getUser, deleteUser } from '@/http/user';
import { userNotFoundOffline } from '@/helpers/user';
import { getCountryName } from '@/helpers/misc';
import { competitionLogoUrl } from '@/helpers/competition';
import store from '@/store';
import UserAdminButtons from '@/components/UserAdminButtons';
import DeletePopover from '@/components/DeletePopover';

export default {
    name: 'UserViewPage',
    components: { IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonChip, IonIcon, IonLabel, IonGrid, IonRow, IonCol, IonButton, IonList, IonItem, IonSkeletonText, UserAdminButtons },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const user = ref();
        const id = parseInt(route.params.id);
        
        // Učitaj podatke korisnika sa servera po IDu iz URLa.
        if(isNaN(id)) return userNotFoundOffline();

        const fetchUser = async () => user.value = await getUser(id) || await userNotFoundOffline();
        
        onIonViewWillEnter(() => fetchUser());

        const pageTitle = computed(() => user.value?.name ? user.value.name : 'Korisnik');

        // Izbriši korisnika, prikaži poruku i otvori popis svih korisnika.
        const onDeleteUser = async () => {
            await deleteUser(id);
            store.dispatch('users/fetch');

            router.push({ name: 'users' });
            toastController.create({ message: 'Korisnik je uspješno izbrisan.', duration: 2000 }).then(t => t.present());
        };

        const deletePopover = ref();
        const openDeletePopover = async (event) => {
            deletePopover.value = await popoverController.create({
                component: DeletePopover,
                componentProps: {
                    delete: onDeleteUser,
                    text: 'Izbriši korisnika',
                    message: 'Jeste li sigurni da želite izbrisati ovog korisnika?'
                },
                event
            }).then(p => p.present());
        };

        const viewCompetition = (id) => router.push({ name: 'view-competition', params: { id }});

        return {
            user,
            pageTitle,
            getCountryName,
            openDeletePopover,
            viewCompetition,
            competitionLogoUrl,
            peopleOutline,
            locationOutline,
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

.competitions {
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