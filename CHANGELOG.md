# Changelog

# Version 2.x

# 2.0.0 

* Remove doctrine annotation support
* Bump minimum PHP version to `8.4`
* Move validation logic to the `ConsumerContainer` class instead of doing that in the `Parser` class
* Add psalm
* The `Consumer` attribute should not be repeatable, as defining multiple attributes would create conflicts
* Change `Annotation` namespace to `Attribute`