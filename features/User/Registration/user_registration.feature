Feature: User registration form
    In order to allow users to create an account on a site
    As a site owner
    I want to expose a user registration form

Scenario: Registration is disabled for users who do not have the "user/register" policy
    Given I do not have the user/register policy
     When I go to "/register"
     Then I see an error message saying that I can not register

Scenario: A new user account can be registered from "/register"
    Given I do have the user/register policy
     When I go to "/register"
      And I fill in the form with valid values
      And I click on the register button
     Then I am on the registration confirmation page
      And I see a registration confirmation message
      And the user account has been created

Scenario: The user group where registered users are created can be customized
     When I register a user account
     Then the user is created in  "TestUserGroup" user group

@broken
Scenario: The user registration templates can be customized
    Given I do have the user/register policy
      And the following user registration templates configuration:
      """
      ezpublish:
        system:
          default:
            user_registration:
              templates:
                form: 'user/registration_form.html.twig'
                confirmation: 'user/registration_confirmation.html.twig'
      """
      And the following template in "templates/user/registration_form.html.twig":
      """
      {% extends no_layout is defined and no_layout == true ? view_base_layout : pagelayout %}

      {% block content %}
          <section class="ez-content-edit">
            {{ form_start(form) }}
            {{- form_widget(form.fieldsData) -}}
            {{ form_end(form) }}
           </section>
      {% endblock %}
      """
      And the following template in "templates/user/registration_confirmation.html.twig":
      """
      {% extends no_layout is defined and no_layout == true ? view_base_layout : pagelayout %}

      {% block content %}
          <h1>Your account has been created</h1>
          <p class="user-register-confirmation-message">
              Thank you for registering an account. You can now <a href="{{ path('login') }}">login</a>.
          </p>
      {% endblock %}
      """
     When I go to "/register"
     Then the form is rendered using the "user/registration_form.html.twig" template
     When I register a user account
     Then the confirmation page is rendered using the "user/registration_confirmation.html.twig" template
