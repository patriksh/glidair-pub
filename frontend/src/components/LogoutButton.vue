<template>
    <ion-button v-if="loggedIn" @click="onLogout">
        <ion-icon :icon="logOutOutline"></ion-icon>
    </ion-button>
</template>

<script>
import { IonButton, IonIcon } from '@ionic/vue';
import { logOutOutline } from 'ionicons/icons';
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import store from '@/store';

export default {
    name: 'LogoutButton',
    components: { IonButton, IonIcon },
    setup() {
        const router = useRouter();
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);

        // Odjavi korisnika i otvori početnu stranicu.
        const onLogout = async () => {
            await store.dispatch('auth/logout');
            router.push({ name: 'home' });
        }

        return {
            loggedIn,
            onLogout,
            logOutOutline
        }
    }
}
</script>