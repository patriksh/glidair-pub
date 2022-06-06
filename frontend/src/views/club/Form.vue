<template>
    <master-layout :title="isEditing ? 'Uredi klub' : 'Novi klub'" :back-button="true">
        <ion-grid class="page" fixed>
            <ion-row class="ion-justify-content-center">
                <ion-col size="12" size-lg="6">
                    <ion-card>
                        <ion-card-content>
                            <form mode="md" @submit.prevent="submitForm">
                                <button type="submit" v-show="false"></button>

                                <LogoInput v-model="club.logo" :loading="loading" />

                                <ion-item>
                                    <ion-label position="floating">Ime</ion-label>
                                    <ion-input v-model="club.name" ref="input" :disabled="loading"></ion-input>
                                    <ion-note v-if="v$.name.$errors.length">Ime je obavezno.</ion-note>
                                </ion-item>
                            </form>
                        </ion-card-content>
                    </ion-card>
                </ion-col>
            </ion-row>
        </ion-grid>
        <WideFab @click="submitForm" :loading="loading">Spremi</WideFab>
    </master-layout>
</template>

<script>
import { IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, onIonViewWillEnter, toastController } from '@ionic/vue';
import { onMounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { getClub, createClub, updateClub } from '@/http/club';
import { clubNotFoundOffline } from '@/helpers/club';
import store from '@/store';
import LogoInput from '@/components/LogoInput';
import WideFab from '@/components/WideFab';

export default {
    name: 'ClubFormPage',
    components: { IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, LogoInput, WideFab },
    setup() {
        const router = useRouter();
        const route = useRoute();

        const loading = ref(false);

        const emptyClub = {
            logo: '',
            name: ''
        };

        const club = ref({ ...emptyClub });

        // Postavi pravila validacije (ime je obavezno).
        const v$ = useVuelidate({
            name: { required },
        }, club);

        // Ako je ime routea "edit-club" onda uređujemo klub. Uzmi ID kluba iz URLa.
        const isEditing = route.name == 'edit-club';
        const editingId = parseInt(route.params.id);

        // Ako uređujemo klub, učitaj njegove podatke sa servera.
        if(isEditing) {
            if(isNaN(editingId)) return clubNotFoundOffline();

            const fetchClub = async () => {
                loading.value = true;
                club.value = await getClub(editingId) || await clubNotFoundOffline();
                loading.value = false;
            }

            onIonViewWillEnter(() => fetchClub());
        }

        // Automatski "klikni" na input za unos imena kluba ako dodajemo novi klub.
        const input = ref();
        onMounted(() => {
            if(!isEditing) {
                setTimeout(() => input.value.$el.setFocus(), 250);
            }
        });

        // Pošalji podatke serveru.
        const submitForm = async () => {
            if(!await v$.value.$validate()) return;

            loading.value = true;

            // Dodaj sve ulazne podatke u FormData objekt (koji je potreban jer šaljemo datoteku - logo).
            const formData = new FormData();
            for(const prop in emptyClub) formData.append(prop, club.value[prop] || '');

            let routeId, toastMessage;
            if(isEditing) {
                routeId = editingId;
                toastMessage = 'Klub je uspješno uređen.';
                await updateClub(editingId, formData);
            } else {
                routeId = editingId;
                toastMessage = 'Klub je uspješno dodan.';
                await createClub(formData).then((created) => routeId = created.id);
            }

            // Prikaži novo dodani/uređeni klub i poruku.
            router.push({ name: 'view-club', params: { id: routeId }});
            toastController.create({ message: toastMessage, duration: 2000 }).then(t => t.present());

            // Osvježi popis svih klubova.
            store.dispatch('clubs/fetch');

            // Resetiraj inpute, stanje učitavanja i validaciju inputa.
            club.value = { ...emptyClub };
            loading.value = false;
            v$.value.$reset();
        }

        return {
            club,
            isEditing,
            submitForm,
            loading,
            input,
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
</style>