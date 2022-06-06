<template>
    <template v-if="$route.name == 'login'">
        <master-layout title="Prijava" :back-button="true">
            <ion-card>
                <ion-card-content>
                    <form mode="md" @submit.prevent="submitForm">
                        <button type="submit" v-show="false"></button>
                        <ion-item class="mb">
                            <ion-label position="floating">Korisničko ime</ion-label>
                            <ion-input v-model="credentials.username" inputmode="email"></ion-input>
                            <ion-note v-if="v$.username.$errors.length">Korisničko ime je obavezno.</ion-note>
                        </ion-item>
                        <ion-item>
                            <ion-label position="floating">Zaporka</ion-label>
                            <ion-input v-model="credentials.password" type="password"></ion-input>
                            <ion-note v-if="v$.password.$errors.length">Zaporka je obavezna.</ion-note>
                        </ion-item>
                    </form>
                </ion-card-content>
            </ion-card>
            <WideFab @click="submitForm" :loading="loading">Prijavi se</WideFab>
        </master-layout>
    </template>
    <template v-else>
        <ion-card-content>
            <form mode="md" @submit.prevent="submitForm">
                <button type="submit" v-show="false"></button>
                <ion-item class="mb">
                    <ion-label position="floating">Korisničko ime</ion-label>
                    <ion-input v-model="credentials.username" inputmode="email"></ion-input>
                    <ion-note v-if="v$.username.$errors.length">Korisničko ime je obavezno.</ion-note>
                </ion-item>
                <ion-item class="mb">
                    <ion-label position="floating">Zaporka</ion-label>
                    <ion-input v-model="credentials.password" type="password"></ion-input>
                    <ion-note v-if="v$.password.$errors.length">Zaporka je obavezna.</ion-note>
                </ion-item>
            </form>
            <ion-button @click="submitForm" :disabled="loading" expand="block">
                <ion-spinner v-if="loading" name="dots"></ion-spinner>
                <template v-else>Prijavi se</template>
            </ion-button>
        </ion-card-content>
    </template>
</template>

<script>
import { IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonButton, IonSpinner, toastController, modalController } from '@ionic/vue';
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { login } from '@/http/auth';
import { isOnline } from '@/helpers/network';
import store from '@/store';
import WideFab from '@/components/WideFab';

export default {
    name: 'LoginPage',
    components: { IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonButton, IonSpinner, WideFab },
    setup() {
        const router = useRouter();
        const route = useRoute();

        const emptyCredentials = {
            username: '',
            password: ''
        };

        const credentials = ref({ ...emptyCredentials });

        // Postavi pravila validacije (korisničko ime i lozinka su obavezni).
        const v$ = useVuelidate({
            username: { required },
            password: { required }
        }, credentials);

        const loading = ref(false);

        // Pošalji podatke serveru.
        const submitForm = async () => {
            if(!await v$.value.$validate()) return;

            loading.value = true;

            if(!await isOnline()) {
                loading.value = false;
                return toastController.create({ message: 'Nema internetske veze.', duration: 2000 }).then(t => t.present());
            }

            const response = await login(credentials.value);

            // Prikaži poruku sa servera ako je prijava neuspješna.
            if(!response.status) {
                loading.value = false;
                return toastController.create({ message: response.message, duration: 2000 }).then(t => t.present());
            }

            // Spremi token.
            await store.dispatch('auth/login', response.token);

            loading.value = false;

            if(store.getters['auth/isLoggedIn']) {
                // Sakrij modal ako je pristuan.
                if(route.name != 'login') {
                    modalController.dismiss();
                } else {
                    router.push({ name: 'competitions' });
                }

                // Resetiraj inpute i validaciju inputa.
                credentials.value = { ...emptyCredentials };
                v$.value.$reset();
            } else {
                toastController.create({ message: 'Došlo je do greške prilikom prijave.', duration: 2000 }).then(t => t.present());
            }
        };

        return {
            credentials,
            submitForm,
            loading,
            v$
        };
    }
}
</script>

<style lang="scss" scoped>
@import '@/theme/forms.scss';

ion-content {
    --padding-bottom: 52px;
}

.mb {
    margin-bottom: 16px;
}
</style>