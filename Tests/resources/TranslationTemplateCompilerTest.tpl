{block name='page_title'}
  {l s='The page you are looking for was not found.' d='errors'}
{/block}

{block name='page_content_container'}
  <section id="content" class="page-content page-not-found">
    <h4>{l s='Sorry for inconvenience.' d='apologies'}</h4>
    <p>{l s='Search again what you are looking for' d='advices'}</p>

  </section>
{/block}

{a foo='bar'} {l foo="bar" s="Yep nope" l="" b="a"}

{l s=$d d=$e}