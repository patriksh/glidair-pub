<template>
    <ion-app>
        <ion-router-outlet></ion-router-outlet>
    </ion-app>
</template>

<script>
import { IonApp, IonRouterOutlet } from '@ionic/vue';
import { attachOfflineSyncListener } from '@/http/offlineSync';

export default {
    name: 'App',
    components: { IonApp, IonRouterOutlet },
    setup() {
        attachOfflineSyncListener();
    }
}
</script>

<style lang="scss">
@import '@/theme/global.scss';
</style>