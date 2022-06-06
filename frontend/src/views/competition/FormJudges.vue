<template>
    <ion-page>
        <ion-content>
            <ion-grid class="page" fixed>
                <ion-row class="ion-justify-content-center">
                    <ion-col size="12" size-lg="6">
                        <ion-card>
                            <CompetitionFormHeader />
                            <ion-card-content>
                                <ion-item mode="md">
                                    <ion-label position="floating">Direktor</ion-label>
                                    <ion-input v-model="director" :disabled="loading"></ion-input>
                                </ion-item>
                                <hr>

                                <ion-grid>
                                    <ion-row v-for="judge in judges" :key="judge.id">
                                        <ion-col size="8" class="name">
                                            <ion-item mode="md">
                                                <ion-input v-model="judge.name" placeholder="Ime" :disabled="loading"></ion-input>
                                            </ion-item>
                                        </ion-col>
                                        <ion-col size="4" class="role">
                                            <ion-item mode="md">
                                                <ion-input v-model="judge.role" placeholder="Funkcija" :disabled="loading"></ion-input>
                                            </ion-item>
                                        </ion-col>
                                    </ion-row>
                                </ion-grid>
                            </ion-card-content>
                        </ion-card>
                    </ion-col>
                </ion-row>
            </ion-grid>
        </ion-content>
        <CompetitionFormNavigation @click-add="addJudge" @click-continue="submitForm" :loading="loading" />
    </ion-page>
</template>

<script>
import { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, onIonViewWillEnter } from '@ionic/vue';
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import equal from 'fast-deep-equal';
import { getCompetition, updateCompetitionJudges } from '@/http/competition';
import { competitionNotFoundOffline } from '@/helpers/competition';
import CompetitionFormHeader from '@/components/CompetitionFormHeader';
import CompetitionFormNavigation from '@/components/CompetitionFormNavigation';

export default {
    name: 'CompetitionFormJudgesPage',
    components: { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonItem, IonLabel, IonInput, CompetitionFormHeader, CompetitionFormNavigation },
    setup() {
        const route = useRoute();
        const router = useRouter();

        const loading = ref(false);
        const id = parseInt(route.params.id);
        const competition = ref();
        const director = ref();
        const emptyJudge = { name: '', role: '' };
        const judges = ref([{ ...emptyJudge }]);
        let originalDirector = null;
        let originalJudges = null;

        const addJudge = () => judges.value.push({ name: '', role: '' });

        // Učitaj podatke natjecanja sa servera po IDu iz URLa.
        const fetchCompetition = async () => {
            if(isNaN(id)) return competitionNotFoundOffline();
            
            loading.value = true;
            competition.value = await getCompetition(id) || await competitionNotFoundOffline();

            director.value = competition.value.director;

            if(competition.value.judges.length) {
                judges.value = [];

                for(const judge of competition.value.judges) {
                    judges.value.push({ name: judge.name, role: judge.role });
                }
                
                judges.value.push(emptyJudge);
            }

            originalDirector = director.value;
            originalJudges = judges.value.map(o => ({ ...o }));
            loading.value = false;
        };

        onIonViewWillEnter(() => fetchCompetition());

        const submitForm = async () => {
            // Pošalji podatke serveru samo ako ima promjena.
            if(director.value != originalDirector || !equal(judges.value, originalJudges)) {
                loading.value = true;
                await updateCompetitionJudges(competition.value.id, { director: director.value || '', judges: judges.value });
                loading.value = false;
            }

            router.push({ name: 'competition-form-participants', params: { id }});
        };

        return {
            loading,
            director,
            judges,
            addJudge,
            submitForm
        }
    }
}
</script>

<style lang="scss" scoped>
@import '@/theme/forms.scss';

ion-content {
    --padding-bottom: 52px;
}

ion-card-content {
    padding-top: 20px
}

ion-grid {
    padding: 0;

    ion-row {
        margin-bottom: 12px;
    }

    ion-row:last-child {
        margin-bottom: 0;
    }

    ion-col {
        padding: 0;

        &.name {
            padding-right: 6px;
        }

        &.role {
            padding-left: 6px;
        }
    }

    ion-item::part(native) {
        height: 60.8px;
    }
}
</style>