<template>
    <ion-infinite-scroll threshold="50%" @ionInfinite="infinite">
        <ion-infinite-scroll-content></ion-infinite-scroll-content>
    </ion-infinite-scroll>
</template>

<script>
import { IonInfiniteScroll, IonInfiniteScrollContent } from '@ionic/vue';
import { refreshOutline } from 'ionicons/icons';

export default {
    name: 'InfiniteScroll',
    components: { IonInfiniteScroll, IonInfiniteScrollContent },
    props: ['infinite'],
    setup() {
        return { refreshOutline }
    }
}
</script>