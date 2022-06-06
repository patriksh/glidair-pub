<template>
    <ion-fab vertical="bottom" horizontal="center" slot="fixed" mode="md">
        <ion-fab-button @click="$emit('click')" :disabled="loading">
            <ion-spinner v-if="loading" name="dots"></ion-spinner>
            <slot v-else></slot>
        </ion-fab-button>
    </ion-fab>
</template>

<script>
import { IonFab, IonFabButton, IonSpinner } from '@ionic/vue';

export default {
    name: 'WideFab',
    components: { IonFab, IonFabButton, IonSpinner },
    props: ['loading'],
    emits: ['click']
}
</script>

<style lang="scss" scoped>
ion-fab {
    margin-left: -100px;
    margin-inline-start: -100px;

    ion-fab-button {
        width: 200px;
        font-size: 18px;
        --border-radius: 10px;
    }
}
</style>