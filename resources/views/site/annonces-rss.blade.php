<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{{ asset('rss.xsl') }}"?>
<rss version="2.0">
  <channel>
    <title>Annonces — {{ $nom }}</title>
    <link>{{ route('site.annonces') }}</link>
    <description>Les dernières annonces du {{ $nom }}.</description>
    <language>fr</language>
    <atom:link xmlns:atom="http://www.w3.org/2005/Atom" href="{{ route('site.annonces.rss') }}" rel="self" type="application/rss+xml"/>
@foreach ($annonces as $a)
    <item>
      <title>{{ $a->titre }}</title>
      <link>{{ route('site.annonces').'#annonce-'.$a->id }}</link>
      <guid isPermaLink="false">annonce-{{ $a->id }}</guid>
      <pubDate>{{ $a->date_publication->startOfDay()->toRfc2822String() }}</pubDate>
      <description>{{ $a->contenu }}</description>
    </item>
@endforeach
  </channel>
</rss>
