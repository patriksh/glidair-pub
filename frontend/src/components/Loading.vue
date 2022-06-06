<template>
    <div class="loading" :style="`height: ${height ? height : '100%'}`">
        <ion-spinner name="crescent"></ion-spinner>
    </div>
</template>

<script>
import { IonSpinner } from '@ionic/vue';

export default {
    name: 'Loading',
    components: { IonSpinner },
    props: ['height']
}
</script>

<style lang="scss" scoped>
.loading {
    display: flex;
    justify-content: center;
    align-items: center;

    ion-spinner {
        width: 2.5em;
        height: 2.5em;
    }
}
</style>