<template>
    <ion-refresher slot="fixed" @ionRefresh="refresh">
        <ion-refresher-content :pulling-icon="refreshOutline"></ion-refresher-content>
    </ion-refresher>
</template>

<script>
import { IonRefresher, IonRefresherContent  } from '@ionic/vue';
import { refreshOutline } from 'ionicons/icons';

export default {
    name: 'PullToRefresh',
    components: { IonRefresher, IonRefresherContent },
    props: ['refresh'],
    setup() {
        return { refreshOutline }
    }
}
</script>