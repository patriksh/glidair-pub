<template>
    <ion-card v-if="loggedIn">
        <ion-grid>
            <ion-row>
                <ion-col size="12">
                    <template v-if="club"><ion-button expand="block" @click="editClub">Uredi klub</ion-button></template>
                    <ion-skeleton-text v-else style="line-height: 44.8px;"></ion-skeleton-text>
                </ion-col>
            </ion-row>
        </ion-grid>
    </ion-card>
</template>

<script>
import { IonCard, IonGrid, IonRow, IonCol, IonButton, IonSkeletonText } from '@ionic/vue';
import { computed } from 'vue'
import { useRouter } from 'vue-router';
import store from '@/store';

export default {
    name: 'ClubAdminButtons',
    components: { IonCard, IonGrid, IonRow, IonCol, IonButton, IonSkeletonText },
    props: ['club'],
    setup(props) {
        const router = useRouter();
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);
        
        const editClub = () => router.push({ name: 'edit-club', params: { id: props.club.id }});

        return {
            loggedIn,
            editClub
        }
    }
}
</script>