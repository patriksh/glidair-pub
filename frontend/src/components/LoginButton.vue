<template>
    <ion-button v-if="!loggedIn" @click="onLogin">
        <ion-icon :icon="logInOutline"></ion-icon>
    </ion-button>
</template>

<script>
import { IonButton, IonIcon, modalController } from '@ionic/vue';
import { logInOutline } from 'ionicons/icons';
import { computed } from 'vue';
import { isDesktop } from '@/helpers/misc';
import store from '@/store';
import LoginPage from '@/views/Login';

export default {
    name: 'LoginButton',
    components: { IonButton, IonIcon },
    props: ['backButton', 'title'],
    setup() {
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);

        // Otvori bottom-sheet modal za prijavu.
        const onLogin = async () => {
            const modal = await modalController.create({
                component: LoginPage,
                initialBreakpoint: !isDesktop() ? 0.35 : undefined,
                breakpoints: !isDesktop() ? [0, 0.35, 1] : undefined
            });

            return modal.present();
        };

        return {
            loggedIn,
            onLogin,
            logInOutline
        }
    }
}
</script>