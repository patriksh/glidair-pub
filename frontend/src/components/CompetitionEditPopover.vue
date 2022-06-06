<template>
    <ion-list lines="none">
        <ion-item @click="go('general')" button>Uredi natjecanje</ion-item>
        <ion-item @click="go('judges')" button>Uredi suce</ion-item>
        <ion-item @click="go('participants')" button>Uredi natjecatelje</ion-item>
        <ion-item @click="go('results')" button>Uredi rezultate</ion-item>
    </ion-list>
</template>

<script>
import { IonList, IonItem, popoverController } from '@ionic/vue';
import { useRouter } from 'vue-router';

export default {
    name: 'CompetitionEditPopover',
    components: { IonList, IonItem },
    props: ['id'],
    setup(props) {
        const router = useRouter();

        // Otvori navedeni korak forme za unos natjecanja.
        const go = (where) => {
            popoverController.dismiss();
            router.push({ name: `competition-form-${where}`, params: { id: props.id }})
        };

        return {
            go
        }
    }
};
</script>