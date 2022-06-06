<template>
    <master-layout :title="pageTitle" :back-button="true">
        <ion-tabs>
            <ion-router-outlet></ion-router-outlet>
        </ion-tabs>
    </master-layout>
</template>

<script>
import { IonTabs, IonRouterOutlet } from '@ionic/vue';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

export default {
    name: 'TabsPage',
    components: { IonTabs, IonRouterOutlet },
    setup() {
        const route = useRoute();

        const pageTitle = computed(() => ('id' in route.params && route.params.id) ? 'Uređivanje natjecanja' : 'Unos natjecanja');

        return {
            pageTitle
        }
    }
}
</script>