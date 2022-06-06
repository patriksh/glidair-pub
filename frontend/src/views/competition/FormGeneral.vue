<template>
    <ion-page>
        <ion-content>
            <ion-grid class="page" fixed>
                <ion-row class="ion-justify-content-center">
                    <ion-col size="12" size-lg="6">
                        <ion-card>
                            <CompetitionFormHeader />
                            <ion-card-content>
                                <form mode="md" @submit.prevent="submitForm" ref="form">
                                    <button ref="submitBtn" type="submit" v-show="false"></button>

                                    <LogoInput v-model="competition.logo" :loading="loading" />

                                    <ion-item>
                                        <ion-label position="floating">Ime</ion-label>
                                        <ion-input v-model="competition.name" :disabled="loading"></ion-input>
                                        <ion-note v-if="v$.name.$errors.length">Ime je obavezno.</ion-note>
                                    </ion-item>

                                    <ion-item>
                                        <ion-label position="floating">Lokacija</ion-label>
                                        <ion-input v-model="competition.location" :disabled="loading"></ion-input>
                                        <ion-note v-if="v$.location.$errors.length">Lokacija je obavezna.</ion-note>
                                    </ion-item>

                                    <ion-item>
                                        <ion-label position="floating">Broj serija</ion-label>
                                        <ion-input v-model="competition.rounds" type="number" :disabled="loading"></ion-input>
                                        <ion-note v-if="v$.rounds.$errors.length">Broj serija je obavezan.</ion-note>
                                    </ion-item>

                                    <ion-item>
                                        <ion-label position="floating">Broj serija koje se ne boduju</ion-label>
                                        <ion-input v-model="competition.rounds_ignored" type="number" :disabled="loading"></ion-input>
                                        <ion-note v-if="v$.rounds_ignored.$errors.length">Broj serija koje se ne boduju je obavezan.</ion-note>
                                    </ion-item>
                                    
                                    <ion-item type="button" id="open-date-modal" :disabled="loading">
                                        <ion-label position="floating">Datum održavanja</ion-label>
                                        <ion-input :value="dateDisplay" readonly></ion-input>
                                        <ion-note v-if="v$.date.$errors.length">Datum održavanja je obavezan.</ion-note>

                                        <ion-modal trigger="open-date-modal" class="date">
                                            <ion-content force-overscroll="false">
                                                <ion-datetime @ionChange="onDateChange" :value="dateISO" presentation="date"></ion-datetime>
                                            </ion-content>
                                        </ion-modal>
                                    </ion-item>
                                </form>
                            </ion-card-content>
                        </ion-card>
                    </ion-col>
                </ion-row>
            </ion-grid>
        </ion-content>
        <CompetitionFormNavigation @click-continue="$refs.submitBtn.click()" :loading="loading" />
    </ion-page>
</template>

<script>
import { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonDatetime, IonModal, onIonViewWillEnter } from '@ionic/vue';
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import useVuelidate from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import { format, parseISO } from 'date-fns';
import equal from 'fast-deep-equal';
import store from '@/store';
import { getCompetition, createCompetition, updateCompetition } from '@/http/competition';
import { competitionNotFoundOffline, competitionDateFormatted } from '@/helpers/competition';
import CompetitionFormHeader from '@/components/CompetitionFormHeader';
import CompetitionFormNavigation from '@/components/CompetitionFormNavigation';
import LogoInput from '@/components/LogoInput';

export default {
    name: 'CompetitionFormGeneralPage',
    components: { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, IonNote, IonDatetime, IonModal, CompetitionFormHeader, CompetitionFormNavigation, LogoInput },
    setup() {
        const router = useRouter();
        const route = useRoute();

        const loading = ref(false);

        const emptyCompetition = {
            logo: '',
            name: '',
            location: '',
            rounds: '',
            rounds_ignored: '',
            date: ''
        };
        
        const competition = ref({ ...emptyCompetition });
        let originalCompetition = null;

        // Postavi pravila validacije (ime, lokacija, broj serija i datum su obavezni).
        const v$ = useVuelidate({
            name: { required },
            location: { required },
            rounds: { required },
            rounds_ignored: { required },
            date: { required },
        }, competition);

        const isEditing = 'id' in route.params && route.params.id;
        const editingId = parseInt(route.params.id);

        // Učitaj natjecanje sa servera ako uređujemo.
        if(isEditing) {
            if(isNaN(editingId)) competitionNotFoundOffline();

            const fetchCompetition = async () => {
                loading.value = true;
                competition.value = await getCompetition(editingId) || await competitionNotFoundOffline();
                originalCompetition = { ...competition.value };
                loading.value = false;
            }

            onIonViewWillEnter(() => fetchCompetition());
        }
        
        // Promijeni format datuma za unos.
        const onDateChange = (event) => {
            if(event.detail.value) {
                competition.value.date = format(parseISO(event.detail.value), 'yyyy-MM-dd');
            }
        };

        // Promijeni format datuma za defaultnu vrijednost za ion-datepicker
        const dateISO = computed(() => (competition.value.date != '') ? format(parseISO(competition.value.date), "yyyy-MM-dd'T'HH:mm:ss.SSSxxx") : null);
        // Promijeni format datuma za prikaz u inputu.
        const dateDisplay = computed(() => (competition.value.date != '') ? competitionDateFormatted(competition.value.date) : null);
        
        // Pošalji podatke serveru.
        const submitForm = async () => {
            if(!await v$.value.$validate()) return;

            // Dodaj sve ulazne podatke u FormData objekt (koji je potreban jer šaljemo datoteku - logo).
            const formData = new FormData();
            for(const prop in emptyCompetition) formData.append(prop, competition.value[prop] || '');

            let routeId;
            if(isEditing) {
                // Pošalji podatke serveru samo ako ima promjena.
                routeId = editingId;
                if(!equal(competition.value, originalCompetition)) {
                    updateCompetition(editingId, formData);
                }
            } else {
                loading.value = true;
                await createCompetition(formData).then((created) => routeId = created.id);
            }

            // Prijeđi na sljedeći korak.
            router.push({ name: 'competition-form-judges', params: { id: routeId }});

            // Osvježi popis svih natjecanja.
            store.dispatch('competitions/fetch');
            
            // Resetiraj inpute, stanje učitavanja i validaciju inputa.
            competition.value = { ...emptyCompetition };
            loading.value = false;
            v$.value.$reset();
        };

        return {
            loading,
            isEditing,
            competition,
            submitForm,
            onDateChange,
            dateISO,
            dateDisplay,
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

ion-card-content {
    padding-top: 20px;
}

ion-item {
    margin-bottom: 16px;
}

ion-modal.date {
    --width: 290px;
    --height: 382px;
    --border-radius: 8px;

    ion-datetime {
        width: auto;
        max-width: 350px;
        height: 382px;
    }
}
</style>