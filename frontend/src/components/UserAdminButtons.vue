<template>
    <ion-card v-if="loggedIn">
        <ion-grid>
            <ion-row>
                <ion-col size="12">
                    <template v-if="user"><ion-button expand="block" @click="editUser">Uredi korisnika</ion-button></template>
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
    name: 'UserAdminButtons',
    components: { IonCard, IonGrid, IonRow, IonCol, IonButton, IonSkeletonText },
    props: ['user'],
    setup(props) {
        const router = useRouter();
        const loggedIn = computed(() => store.getters['auth/isLoggedIn']);
        
        const editUser = () => router.push({ name: 'edit-user', params: { id: props.user.id }});

        return {
            loggedIn,
            editUser
        }
    }
}
</script>