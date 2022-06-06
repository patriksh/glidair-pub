<template>
    <ion-grid class="page" fixed>
        <ion-row>
            <ion-col>
                <slot></slot>
            </ion-col>
        </ion-row>
    </ion-grid>
</template>

<script>
import { IonGrid, IonRow, IonCol } from '@ionic/vue';

export default {
    name: 'ListContainer',
    components: { IonGrid, IonRow, IonCol }
}
</script>