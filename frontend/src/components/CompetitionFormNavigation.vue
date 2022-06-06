<template>
    <ion-fab v-if="buttons.length === 1" class="one" vertical="bottom" horizontal="center" slot="fixed" mode="md">
        <ion-fab-button @click="$emit('click-continue')" :disabled="loading">
            <ion-spinner v-if="loading" name="dots"></ion-spinner>
            <template v-else>{{ buttons[0] }}</template>
        </ion-fab-button>
    </ion-fab>

    <ion-fab v-if="buttons.length === 2" class="two" vertical="bottom" horizontal="start" slot="fixed" mode="md">
        <ion-fab-button @click="$emit('click-add')" color="light" :disabled="loading">
            <ion-spinner v-if="loading" name="dots"></ion-spinner>
            <template v-else>{{ buttons[0] }}</template>
        </ion-fab-button>
    </ion-fab>
    <ion-fab v-if="buttons.length === 2" class="two" vertical="bottom" horizontal="end" slot="fixed" mode="md">
        <ion-fab-button @click="$emit('click-continue')" :disabled="loading">
            <ion-spinner v-if="loading" name="dots"></ion-spinner>
            <template v-else>{{ buttons[1] }}</template>
        </ion-fab-button>
    </ion-fab>
</template>

<script>
import { IonFab, IonFabButton, IonSpinner } from '@ionic/vue';
import { useRoute } from 'vue-router';

export default {
    name: 'CompetitionFormNavigation',
    components: { IonFab, IonFabButton, IonSpinner },
    props: ['loading'],
    emits: ['click-continue', 'click-add'],
    setup() {
        const route = useRoute();

        const step = route.meta.step;
        const stepButtons = {
            general: ['Nastavi'],
            judges: ['Dodaj sudca', 'Nastavi'],
            participants: ['Dodaj natjecatelja', 'Nastavi'],
            results: ['Spremi']
        }

        // Prikaži buttone ovisno o trenutnom koraku.
        const buttons = step in stepButtons ? stepButtons[step] : [];

        return {
            buttons
        }
    }
}
</script>

<style lang="scss" scoped>
ion-fab-button {
    font-size: 18px;
    --border-radius: 10px;
}

.one {
    margin-left: -100px;
    margin-inline-start: -100px;

    ion-fab-button {
        width: 200px;
    }
}

.two ion-fab-button {
    width: 175px;
}

@media(max-width: 380px) {
    .two ion-fab-button {
        width: 150px;
    }
}

@media(max-width: 330px) {
    .two ion-fab-button {
        width: 135px;
    }
}

@media(max-width: 280px) {
    .two ion-fab-button {
        width: 125px;
    }
}
</style>