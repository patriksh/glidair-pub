<template>
    <ion-page>
        <ion-content>
            <ion-grid class="page" fixed>
                <ion-row class="ion-justify-content-center">
                    <ion-col size="12" size-lg="6">
                        <ion-card v-if="competition">
                            <CompetitionFormHeader />
                            <ion-card-content>
                                <ion-item class="round-select" lines="none">
                                    <ion-label>Serija</ion-label>
                                    <ion-select v-model="round" cancel-text="Odustani" :disabled="loading">
                                        <ion-select-option v-for="index in competition.rounds" :key="index" :value="index">{{ index }}</ion-select-option>
                                    </ion-select>
                                </ion-item>

                                <ion-list class="score-list">
                                    <ion-item v-for="participant in competition.participants" :key="participant.id">
                                        <ion-label>{{ participant.user.name }}</ion-label>
                                        <ion-input type="number" v-model="scores[participant.id][round]" :disabled="loading"></ion-input>
                                    </ion-item>
                                </ion-list>
                            </ion-card-content>
                        </ion-card>
                    </ion-col>
                </ion-row>
            </ion-grid>
        </ion-content>
        <CompetitionFormNavigation @click-continue="submitForm" :loading="loading" />
    </ion-page>
</template>

<script>
import { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonSelect, IonSelectOption, IonList, IonInput, onIonViewWillEnter } from '@ionic/vue';
import { addOutline, removeOutline, checkmarkOutline } from 'ionicons/icons';
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import equal from 'fast-deep-equal';
import store from '@/store';
import { competitionNotFound } from '@/helpers/competition';
import CompetitionFormHeader from '@/components/CompetitionFormHeader';
import CompetitionFormNavigation from '@/components/CompetitionFormNavigation';

export default {
    name: 'CompetitionParticipantsFormPage',
    components: { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonSelect, IonSelectOption, IonList, IonInput, CompetitionFormHeader, CompetitionFormNavigation },
    setup() {
        const router = useRouter();
        const route = useRoute();
        
        const loading = ref(false);
        const id = parseInt(route.params.id);
        const competition = ref();
        const round = ref(1);
        const scores = ref({});
        let originalScores = {};

        // Učitaj podatke natjecanja sa servera po IDu iz URLa.
        const fetchCompetition = async () => {
            if(isNaN(id)) return competitionNotFound();
            
            loading.value = true;
            competition.value = await store.dispatch('competitions/fetchSingle', id) || await competitionNotFound();

            // Popuni varijablu s rezultatima za svakog natjecatelja kako bi ispravno mogli koristiti v-model.
            const emptyParticipantScores = Array(competition.value.rounds).fill(null);
            for(const participant of competition.value.participants) {
                scores.value[participant.id] = [null, ...emptyParticipantScores];

                for(const round of participant.rounds) {
                    scores.value[participant.id][round.round] = round.score;
                }

                originalScores[participant.id] = [...scores.value[participant.id]];
            }

            loading.value = false;
        };

        onIonViewWillEnter(() => fetchCompetition());

        const submitForm = async () => {
            // Pošalji podatke serveru samo ako ima promjena.
            if(!equal(scores.value, originalScores)) {
                loading.value = true;
                await store.dispatch('competitions/updateRounds', { id: competition.value.id, rounds: scores.value });
                loading.value = false;
            }

            router.push({ name: 'view-competition', params: { id }});
        };

        return {
            loading,
            competition,
            round,
            scores,
            submitForm,
            addOutline,
            removeOutline,
            checkmarkOutline
        }
    }
}
</script>

<style lang="scss" scoped>
ion-content {
    --padding-bottom: 52px;
}

ion-card-content {
    padding-top: 20px
}

ion-item {
    --padding-start: 0;
    --padding-end: 0;
    --inner-padding-start: 15px;
    --inner-padding-end: 0;
}

.round-select {
    background: var(--ion-color-light);
    border-radius: 8px;
    margin-bottom: 16px;
}

.score-list {
    ion-input {
        text-align: right;
    }
}
</style>