<script lang="ts">
  import { onMount } from 'svelte'
  import { fetch, t, notify } from 'blessing-skin'

  let description = ''
  let raw = ''
  $: isEmptyDescription = description.trim() === ''

  export let tid: string
  export let canEdit = false
  let isEditing = false
  let isSubmitting = false
  export let maxLength = Infinity
  $: isLengthExceeded = (raw?.length ?? 0) > maxLength

  onMount(async () => {
    description = await fetch.get(`/texture/${tid}/description`)
  })

  async function editDescription() {
    const response: string | { message: string } = await fetch.get(
      `/texture/${tid}/description`,
      {
        raw: true,
      },
    )
    if (typeof response === 'string') {
      raw = response
      isEditing = true
    } else {
      notify.toast.error(response.message)
      return
    }
  }

  async function submitDescription() {
    isSubmitting = true
    const response: string | { message: string } = await fetch.put(
      `/texture/${tid}/description`,
      {
        description: raw,
      },
    )
    isSubmitting = false
    if (typeof response === 'string') {
      description = response
      isEditing = false
    } else {
      notify.toast.error(response.message)
    }
  }
</script>

<style>
  description-content :global(h1),
  description-content :global(h2) {
    padding-bottom: 0.3rem;
    border-bottom: 1px solid #eaecef;
  }

  description-content :global(blockquote) {
    color: #aaa;
    border-left: 0.3rem solid #aaa;
  }

  description-content :global(hr) {
    border-top-width: 4px;
  }
</style>

<div class="card card-secondary">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h3 class="card-title">{t('texture-description.description')}</h3>
      {#if canEdit && !isEditing}
        <button
          class="btn btn-secondary btn-sm float-right"
          title={t('texture-description.edit')}
          on:click={editDescription}>
          <i class="fas fa-edit" />
        </button>
      {/if}
    </div>
  </div>
  <div class="card-body">
    {#if isEditing}
      <textarea class="form-control" rows="10" bind:value={raw} />
    {:else if isEmptyDescription}
      <p>
        <i>{t('texture-description.empty')}</i>
      </p>
    {:else}
      <description-content>
        {@html description}
      </description-content>
    {/if}
  </div>
  {#if isEditing}
    <div class="card-footer">
      {#if isLengthExceeded}
        <div class="alert alert-info">
          {t('texture-description.exceeded', { max: maxLength })}
        </div>
      {/if}
      <div class="d-flex justify-content-between">
        <button
          class="btn btn-primary"
          disabled={isSubmitting || isLengthExceeded}
          on:click={submitDescription}>
          {#if isSubmitting}
            <span>
              <i class="fas fa-sync fa-spin" />
            </span>
          {:else}{t('general.submit')}{/if}
        </button>
        <button
          class="btn btn-secondary"
          disabled={isSubmitting}
          on:click={() => (isEditing = false)}>
          {t('general.cancel')}
        </button>
      </div>
    </div>
  {/if}
</div>
