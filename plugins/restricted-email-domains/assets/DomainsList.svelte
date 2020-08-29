<script lang="ts">
  import { createEventDispatcher } from 'svelte'
  import { t } from 'blessing-skin'

  type Item = { id: string; value: string }

  const dispath = createEventDispatcher()

  export let title = ''
  export let cardType = ''
  export let isDirty: boolean
  export let isLoading: boolean
  export let list: Item[] = []
</script>

<div class={`card card-${cardType}`}>
  <div class="card-header">
    <h3 class="card-title">
      {title}
      {#if isDirty}
        <span class="ml-1">●</span>
      {/if}
    </h3>
  </div>
  <div class="card-body">
    {#if isLoading}
      <div class="text-center">
        <i class="fas fa-sync fa-spin" />
      </div>
    {:else}
      {#each list as item (item.id)}
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">@</span>
          </div>
          <input
            type="text"
            class="form-control"
            bind:value={item.value}
            on:input={() => dispath('edit')} />
          <div class="input-group-append">
            <button
              class="btn btn-secondary"
              on:click={() => dispath('remove', item.id)}>
              <i class="fas fa-trash" />
            </button>
          </div>
        </div>
      {:else}
        <div class="text-center">
          <p>{t('general.noResult')}</p>
        </div>
      {/each}
    {/if}
    <button class="btn btn-primary mt-3" on:click={() => dispath('add')}>
      <i class="fas fa-plus" />
    </button>
  </div>
  <div class="card-footer">
    <div class="float-right">
      <button class="btn btn-primary" on:click={() => dispath('save')}>
        {t('general.submit')}
      </button>
    </div>
  </div>
</div>
