<template>
    <ion-page mode="md">
        <ion-tabs>
            <ion-router-outlet></ion-router-outlet>
            <ion-tab-bar class="tab-bar" :slot="tabBarSlot" v-if="!route.meta.hideTabBar">
                <ion-tab-button tab="competitions" href="/tabs/competitions" :layout="tabButtonLayout">
                    <ion-icon :icon="ribbonOutline"></ion-icon>
                    <ion-label>Natjecanja</ion-label>
                </ion-tab-button>

                <ion-tab-button v-if="loggedIn" tab="users" href="/tabs/users" :layout="tabButtonLayout">
                    <ion-icon :icon="personOutline"></ion-icon>
                    <ion-label>Korisnici</ion-label>
                </ion-tab-button>

                <ion-tab-button v-if="loggedIn && isDesktop" @click="onFabClick" :layout="tabButtonLayout">
                    <ion-icon :icon="addOutline"></ion-icon>
                    <ion-label>{{ addLabel }}</ion-label>
                </ion-tab-button>

                <ion-tab-button v-if="loggedIn && !isDesktop" tab="fake"></ion-tab-button>
                <ion-fab v-if="loggedIn && !isDesktop" vertical="end" horizontal="center">
                    <ion-fab-button @click="onFabClick">
                        <ion-icon :icon="addOutline"></ion-icon>
                    </ion-fab-button>
                </ion-fab>

                <ion-tab-button v-if="loggedIn" tab="clubs" href="/tabs/clubs" :layout="tabButtonLayout">
                    <ion-icon :icon="peopleOutline"></ion-icon>
                    <ion-label>Klubovi</ion-label>
                </ion-tab-button>

                <ion-tab-button tab="leaderboard" href="/tabs/leaderboard" :layout="tabButtonLayout">
                    <ion-icon :icon="podiumOutline"></ion-icon>
                    <ion-label>Rezultati</ion-label>
                </ion-tab-button>
            </ion-tab-bar>
        </ion-tabs>
    </ion-page>
</template>

<script>
import { IonPage, IonTabs, IonRouterOutlet, IonTabBar, IonTabButton, IonIcon, IonLabel, IonFab, IonFabButton } from '@ionic/vue';
import { ribbonOutline, personOutline, addOutline, peopleOutline, podiumOutline, trophyOutline  } from 'ionicons/icons';
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { isDesktop } from '@/helpers/misc';
import store from '@/store';

export default {
    name: 'TabsPage',
    components: { IonPage, IonTabs, IonRouterOutlet, IonTabBar, IonTabButton, IonIcon, IonLabel, IonFab, IonFabButton },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);

        const tabBarSlot = isDesktop() ? 'top' : 'bottom';
        const tabButtonLayout = isDesktop() ? 'icon-start' : 'label-hide';

        const addLabel = computed(() => {
            const path = route.path;
            if(path.includes('competitions') || path.includes('leaderboard')) {
                return 'Dodaj natjecanje';
            } else if(path.includes('users')) {
                return 'Dodaj korisnika';
            } else if(path.includes('clubs')) {
                return 'Dodaj klub';
            }

            return '';
        });

        // Ovisno o trenutnom tabu, button "+" otvara formu za dodavanje natjecanja, korisnika ili kluba.
        const onFabClick = () => {
            const path = route.path;
            if(path.includes('competitions') || path.includes('leaderboard')) {
                router.push({ name: 'competition-form' });
            } else if(path.includes('users')) {
                router.push({ name: 'add-user' });
            } else if(path.includes('clubs')) {
                router.push({ name: 'add-club' });
            }
        };

        return {
            route,
            isDesktop,
            tabBarSlot,
            tabButtonLayout,
            addLabel,
            onFabClick,
            loggedIn,
            ribbonOutline,
            personOutline,
            addOutline,
            peopleOutline,
            podiumOutline,
            trophyOutline,
        }
    }
}
</script>

<style lang="scss" scoped>
ion-tab-bar {
    --background: var(--ion-color-light);
    --color: var(--ion-color-dark);

    ion-tab-button {
        &.tab-selected {
            color: var(--ion-color-primary);
        }
    }
}

ion-fab {
    margin-inline-start: -20px;

    ion-fab-button {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
}
</style>