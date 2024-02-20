<template >
  <v-container fluid class="fill-height">
    <v-card rounded="l" class="mb-4">
      <v-row no-gutters align="center">
        <v-col cols="12" md="6">
          <v-card-title>
            <v-icon large left> mdi-twitter </v-icon>
            <span class="text-h6 font-weight-light">Twitter</span>
            <v-spacer />
            <div align="center" justify="end">
              <v-icon class="mr-1"> mdi-heart </v-icon>
              <span class="subheading mr-2">256</span>
              <span class="mr-1">·</span>
              <v-icon class="mr-1"> mdi-share-variant </v-icon>
              <span class="subheading">45</span>
            </div>
          </v-card-title>

          <v-card-text class="text-h5 font-weight-bold">
            "Turns out semicolon-less style is easier and safer in TS because
            most gotcha edge cases are type invalid as well."
          </v-card-text>

          <v-card-actions>
            <v-list-item class="grow">
              <v-row align="center" justify="end">
                <v-icon class="mr-1"> mdi-heart </v-icon>
                <span class="subheading mr-2">256</span>
                <span class="mr-1">·</span>
                <v-icon class="mr-1"> mdi-share-variant </v-icon>
                <span class="subheading">45</span>
              </v-row>
            </v-list-item>
          </v-card-actions>
        </v-col>
        <v-divider vertical />
        <v-col cols="12" md="6">
          <v-card-title>
            <v-icon large left> mdi-twitter </v-icon>
            <span class="text-h6 font-weight-light">Twitter</span>
          </v-card-title>

          <v-card-text class="text-h5 font-weight-bold">
            "Turns out semicolon-less style is easier and safer in TS because
            most gotcha edge cases are type invalid as well."
          </v-card-text>

          <v-card-actions>
            <v-list-item class="grow">
              <v-list-item-avatar color="grey darken-3">
                <v-img
                  class="elevation-6"
                  alt=""
                  src="https://avataaars.io/?avatarStyle=Transparent&topType=ShortHairShortCurly&accessoriesType=Prescription02&hairColor=Black&facialHairType=Blank&clotheType=Hoodie&clotheColor=White&eyeType=Default&eyebrowType=DefaultNatural&mouthType=Default&skinColor=Light"
                ></v-img>
              </v-list-item-avatar>

              <v-list-item-content>
                <v-list-item-title>Evan You</v-list-item-title>
              </v-list-item-content>

              <v-row align="center" justify="end">
                <v-icon class="mr-1"> mdi-heart </v-icon>
                <span class="subheading mr-2">256</span>
                <span class="mr-1">·</span>
                <v-icon class="mr-1"> mdi-share-variant </v-icon>
                <span class="subheading">45</span>
              </v-row>
            </v-list-item>
          </v-card-actions>
        </v-col>
      </v-row>
    </v-card>
    <v-row>
      <v-col>
        <v-card class="pa-2" outlined rounded="xl">
          <v-card-title> Actividades </v-card-title>
          <v-card-text>
            <v-timeline dense clipped>
              <v-timeline-item
                fill-dot
                class="white--text mb-12"
                color="orange"
                large
              >
                <template v-slot:icon>
                  <span>JL</span>
                </template>
                <v-text-field
                  v-model="input"
                  hide-details
                  flat
                  label="Leave a comment..."
                  solo
                  @keydown.enter="comment"
                >
                  <template v-slot:append>
                    <v-btn class="mx-0" depressed @click="comment">
                      Post
                    </v-btn>
                  </template>
                </v-text-field>
              </v-timeline-item>

              <v-slide-x-transition group>
                <v-timeline-item
                  v-for="event in timeline"
                  :key="event.id"
                  class="mb-4"
                  color="pink"
                  small
                >
                  <v-row justify="space-between">
                    <v-col cols="7" v-text="event.text"></v-col>
                    <v-col
                      class="text-right"
                      cols="5"
                      v-text="event.time"
                    ></v-col>
                  </v-row>
                </v-timeline-item>
              </v-slide-x-transition>

              <v-timeline-item class="mb-6" hide-dot>
                <span>TODAY</span>
              </v-timeline-item>

              <v-timeline-item
                class="mb-4"
                color="grey"
                icon-color="grey lighten-2"
                small
              >
                <v-row justify="space-between">
                  <v-col cols="7"> This order was archived. </v-col>
                  <v-col class="text-right" cols="5"> 15:26 EDT </v-col>
                </v-row>
              </v-timeline-item>

              <v-timeline-item class="mb-4" small>
                <v-row justify="space-between">
                  <v-col cols="7">
                    <v-chip class="white--text ml-0" color="purple" label small>
                      APP
                    </v-chip>
                    Digital Downloads fulfilled 1 item.
                  </v-col>
                  <v-col class="text-right" cols="5"> 15:25 EDT </v-col>
                </v-row>
              </v-timeline-item>

              <v-timeline-item class="mb-4" color="grey" small>
                <v-row justify="space-between">
                  <v-col cols="7">
                    A $15.00 USD payment was processed on PayPal Express
                    Checkout
                  </v-col>
                  <v-col class="text-right" cols="5"> 15:25 EDT </v-col>
                </v-row>
              </v-timeline-item>

              <v-timeline-item color="grey" small>
                <v-row justify="space-between">
                  <v-col cols="7">
                    John Leider placed this order on Online Store (checkout
                    #1937432132572).
                  </v-col>
                  <v-col class="text-right" cols="5"> 15:25 EDT </v-col>
                </v-row>
              </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col>
        <v-card class="pa-2" outlined rounded="xl">
          <v-tabs color="deep-purple accent-4" right>
            <v-tab>Landscape</v-tab>
            <v-tab>City</v-tab>
            <v-tab>Abstract</v-tab>

            <v-tab-item v-for="n in 3" :key="n">
              <v-container fluid>
                <v-row>
                  <v-col v-for="i in 6" :key="i" cols="12" md="4">
                    <v-img
                      :src="`https://picsum.photos/500/300?image=${
                        i * n * 5 + 10
                      }`"
                      :lazy-src="`https://picsum.photos/10/6?image=${
                        i * n * 5 + 10
                      }`"
                      aspect-ratio="1"
                    ></v-img>
                  </v-col>
                </v-row>
              </v-container>
            </v-tab-item>
          </v-tabs>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
export default {
  data: () => ({
    events: [],
    input: null,
    nonce: 0,
  }),

  computed: {
    timeline() {
      return this.events.slice().reverse();
    },
  },

  methods: {
    comment() {
      const time = new Date().toTimeString();
      this.events.push({
        id: this.nonce++,
        text: this.input,
        time: time.replace(
          /:\d{2}\sGMT-\d{4}\s\((.*)\)/,
          (match, contents, offset) => {
            return ` ${contents
              .split(" ")
              .map((v) => v.charAt(0))
              .join("")}`;
          }
        ),
      });

      this.input = null;
    },
  },
};
</script>
<style >
</style>