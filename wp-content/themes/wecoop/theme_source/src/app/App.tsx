import { useState, useEffect } from 'react';
import {
  Users,
  GraduationCap,
  Briefcase,
  Smartphone,
  Network,
  Target,
  TrendingUp,
  Heart,
  MapPin,
  Mail,
  Phone,
  Calendar,
  CheckCircle2,
  ArrowRight,
  Building2,
  HandshakeIcon,
  UserCheck,
  UserPlus,
  MessageCircle,
  ClipboardList,
  LogIn,
  Menu,
  X,
  Home,
  Award,
  Layers
} from 'lucide-react';
import wecoopLogo from '@/imports/wecooplogo2.png';
import appWecoopLogo from '@/imports/Recurso_1@3x.png';
import passaparolaLogo from '@/imports/Recurso_3@3x.png';
import aynixLogo from '@/imports/Recurso_2.png';
import heroImage from '@/imports/Firefly_Gemini_Flash_diverse_people_connecting_through_technology_in_a_modern_community_hub,_smartphones_a_903873.png';
import socialWorkerImage from '@/imports/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png';
import mobileAppImage from '@/imports/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png';
import teamImage from '@/imports/Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png';
import platformImage from '@/imports/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png';
import collaborationImage from '@/imports/Recurso_5.png';
import newCollaborationImage from '@/imports/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png';

export default function App() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Prevent body scroll when mobile menu is open
  useEffect(() => {
    if (mobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [mobileMenuOpen]);

  const navItems = [
    { href: '#inicio', label: 'Inicio', icon: Home },
    { href: '#que-es', label: 'Modelo WECOOP', icon: Layers },
    { href: '#passaparola', label: 'Proyecto PASSAPAROLA', icon: Award },
    { href: '#plataforma', label: 'Plataforma Digital', icon: Smartphone },
    { href: '#impacto', label: 'Impacto Social', icon: TrendingUp },
    { href: '#partners', label: 'Partners', icon: HandshakeIcon },
    { href: '#contacto', label: 'Contacto', icon: MapPin }
  ];

  const handleNavClick = () => {
    setMobileMenuOpen(false);
  };

  return (
    <div className="min-h-screen bg-white scroll-smooth">
      {/* Navigation */}
      <nav className="fixed top-0 w-full bg-white/95 backdrop-blur-sm z-50 border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center h-20 gap-4">
            {/* Mobile Menu Button - Left Side */}
            <button
              type="button"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2 text-[#4D4C4C] hover:text-[#1282A8] transition -ml-2 z-50 relative"
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? (
                <X className="w-7 h-7" />
              ) : (
                <Menu className="w-7 h-7" />
              )}
            </button>

            {/* Logo */}
            <a href="#inicio" onClick={handleNavClick} className="flex-shrink-0">
              <img src={wecoopLogo} alt="WECOOP" className="h-16" />
            </a>

            {/* Desktop Navigation */}
            <div className="hidden md:flex space-x-8 flex-1 justify-center">
              <a href="#que-es" className="text-[#4D4C4C] hover:text-[#1282A8] transition">Qué es WECOOP</a>
              <a href="#passaparola" className="text-[#4D4C4C] hover:text-[#1282A8] transition">Passaparola</a>
              <a href="#plataforma" className="text-[#4D4C4C] hover:text-[#1282A8] transition">Plataforma Digital</a>
              <a href="#impacto" className="text-[#4D4C4C] hover:text-[#1282A8] transition">Impacto</a>
              <a href="#contacto" className="text-[#4D4C4C] hover:text-[#1282A8] transition">Contacto</a>
            </div>

            {/* Spacer for mobile to push Colabora button to the right */}
            <div className="flex-1 md:hidden"></div>

            {/* CTA Button - Always Visible */}
            <a
              href="#contacto"
              className="bg-[#1282A8] text-white px-4 py-2 md:px-6 md:py-2.5 rounded-lg hover:bg-[#379AC4] transition text-sm md:text-base whitespace-nowrap z-50 relative"
            >
              Colabora
            </a>
          </div>
        </div>
      </nav>

      {/* Mobile Menu Overlay */}
      {mobileMenuOpen && (
        <div
          className="fixed inset-0 bg-black/30 backdrop-blur-sm z-[60] md:hidden"
          onClick={() => setMobileMenuOpen(false)}
          style={{ top: '80px' }}
        />
      )}

      {/* Mobile Menu - Slide from Left */}
      <div
        className={`fixed left-0 bg-white shadow-2xl transform transition-transform duration-300 ease-out z-[70] md:hidden ${
          mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
        style={{
          top: '80px',
          bottom: '0',
          width: '320px',
          maxWidth: '85vw'
        }}
      >
        <div className="flex flex-col h-full overflow-y-auto">
          <div className="p-6 space-y-2">
            {navItems.map((item, index) => (
              <a
                key={index}
                href={item.href}
                onClick={handleNavClick}
                className="flex items-center gap-4 p-4 rounded-xl hover:bg-[#1282A8]/5 transition-colors group"
              >
                <div className="bg-[#1282A8]/10 p-3 rounded-lg group-hover:bg-[#1282A8] transition-colors">
                  <item.icon className="w-5 h-5 text-[#1282A8] group-hover:text-white transition-colors" />
                </div>
                <span className="text-[#4D4C4C] group-hover:text-[#1282A8] transition-colors font-medium">
                  {item.label}
                </span>
              </a>
            ))}
          </div>
        </div>
      </div>

      {/* Hero Section */}
      <section id="inicio" className="pt-32 pb-20 bg-gradient-to-br from-[#1282A8]/5 via-white to-[#59B575]/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h1 className="text-5xl md:text-6xl font-bold text-[#4D4C4C] mb-6 leading-tight">
                Un ecosistema de <span className="text-[#1282A8]">inclusión</span> y <span className="text-[#59B575]">oportunidades</span>
              </h1>
              <p className="text-xl text-[#7C8788] mb-8 leading-relaxed">
                WECOOP integra servicios territoriales, formación profesional y tecnología digital para conectar personas vulnerables con oportunidades de empleo y desarrollo personal.
              </p>
              <div className="flex flex-wrap gap-4">
                <button className="bg-[#1282A8] text-white px-8 py-4 rounded-lg hover:bg-[#379AC4] transition flex items-center gap-2">
                  Descubre más
                  <ArrowRight className="w-5 h-5" />
                </button>
                <button className="border-2 border-[#1282A8] text-[#1282A8] px-8 py-4 rounded-lg hover:bg-[#1282A8]/5 transition">
                  Contáctanos
                </button>
              </div>
            </div>
            <div className="relative">
              <img src={heroImage} alt="Comunidad WECOOP" className="rounded-2xl shadow-2xl" />
              <div className="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-xl">
                <div className="flex items-center gap-3">
                  <div className="bg-[#59B575]/10 p-3 rounded-lg">
                    <Users className="w-6 h-6 text-[#59B575]" />
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-[#4D4C4C]">400+</div>
                    <div className="text-sm text-[#7C8788]">Beneficiarios</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* What is WECOOP */}
      <section id="que-es" className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-[#4D4C4C] mb-4">¿Qué es WECOOP?</h2>
            <p className="text-xl text-[#7C8788] max-w-3xl mx-auto">
              Un modelo innovador que combina un centro territorial, servicios de orientación, formación, oportunidades laborales y una plataforma digital integrada.
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            <div className="bg-gradient-to-br from-[#1282A8]/5 to-[#379AC4]/10 p-8 rounded-2xl hover:shadow-lg transition">
              <div className="bg-[#1282A8] w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                <MapPin className="w-8 h-8 text-white" />
              </div>
              <h3 className="text-2xl font-bold text-[#4D4C4C] mb-4">Centro Territorial</h3>
              <p className="text-[#7C8788]">
                Un espacio físico de referencia donde las personas encuentran orientación, acompañamiento personalizado y acceso a servicios de inclusión social.
              </p>
            </div>

            <div className="bg-gradient-to-br from-[#59B575]/5 to-[#8CC163]/10 p-8 rounded-2xl hover:shadow-lg transition">
              <div className="bg-[#59B575] w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                <GraduationCap className="w-8 h-8 text-white" />
              </div>
              <h3 className="text-2xl font-bold text-[#4D4C4C] mb-4">Formación y Empleo</h3>
              <p className="text-[#7C8788]">
                Recorridos formativos personalizados, desarrollo de competencias profesionales y conexión directa con oportunidades laborales reales.
              </p>
            </div>

            <div className="bg-gradient-to-br from-[#E6266B]/5 to-[#EC6B5C]/10 p-8 rounded-2xl hover:shadow-lg transition">
              <div className="bg-[#E6266B] w-16 h-16 rounded-xl flex items-center justify-center mb-6">
                <Smartphone className="w-8 h-8 text-white" />
              </div>
              <h3 className="text-2xl font-bold text-[#4D4C4C] mb-4">Plataforma Digital</h3>
              <p className="text-[#7C8788]">
                Tecnología accesible que permite gestionar citas, acceder a formación, comunicarse con operadores y hacer seguimiento del recorrido personal.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Ecosystem Diagram */}
      <section className="py-20 bg-gradient-to-br from-[#1282A8]/5 to-[#59B575]/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-[#4D4C4C] mb-4">El Modelo Físico + Digital</h2>
            <p className="text-xl text-[#7C8788] max-w-3xl mx-auto">
              Un ecosistema integrado que conecta territorio, personas, servicios y tecnología.
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[
              { icon: MapPin, label: 'Territorio', color: '#1282A8', description: 'Presencia local y comunitaria' },
              { icon: Users, label: 'Personas', color: '#59B575', description: 'En el centro del sistema' },
              { icon: Heart, label: 'Servicios', color: '#E6266B', description: 'Orientación y acompañamiento' },
              { icon: GraduationCap, label: 'Formación', color: '#8CC163', description: 'Desarrollo de competencias' },
              { icon: Briefcase, label: 'Oportunidades', color: '#EC6B5C', description: 'Acceso al empleo' },
              { icon: Network, label: 'Plataforma Digital', color: '#379AC4', description: 'Tecnología inclusiva' }
            ].map((item, index) => (
              <div key={index} className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition">
                <div className="flex items-start gap-4">
                  <div
                    className="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0"
                    style={{ backgroundColor: `${item.color}20` }}
                  >
                    <item.icon className="w-6 h-6" style={{ color: item.color }} />
                  </div>
                  <div>
                    <h3 className="text-lg font-bold text-[#4D4C4C] mb-1">{item.label}</h3>
                    <p className="text-sm text-[#7C8788]">{item.description}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Passaparola Project */}
      <section id="passaparola" className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12 items-center mb-16">
            <div>
              <img src={passaparolaLogo} alt="Passaparola" className="h-48 mb-6" />
              <h2 className="text-4xl font-bold text-[#4D4C4C] mb-6">Proyecto Passaparola</h2>
              <p className="text-xl text-[#7C8788] mb-6">
                Persone, connessioni e opportunità: un proyecto dedicado al apoyo integral de personas migrantes y en situación de vulnerabilidad.
              </p>
            </div>
            <img src={teamImage} alt="Equipo Passaparola" className="rounded-2xl shadow-xl" />
          </div>

          <div className="grid md:grid-cols-4 gap-6">
            <div className="bg-[#E6266B]/5 p-6 rounded-xl">
              <div className="bg-[#E6266B] w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                <Target className="w-6 h-6 text-white" />
              </div>
              <h3 className="font-bold text-[#4D4C4C] mb-2">Problema</h3>
              <p className="text-sm text-[#7C8788]">
                Dificultad de acceso a servicios y oportunidades para personas migrantes y vulnerables.
              </p>
            </div>

            <div className="bg-[#1282A8]/5 p-6 rounded-xl">
              <div className="bg-[#1282A8] w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                <CheckCircle2 className="w-6 h-6 text-white" />
              </div>
              <h3 className="font-bold text-[#4D4C4C] mb-2">Solución</h3>
              <p className="text-sm text-[#7C8788]">
                Sistema integrado de orientación, formación y conexión con oportunidades laborales.
              </p>
            </div>

            <div className="bg-[#59B575]/5 p-6 rounded-xl">
              <div className="bg-[#59B575] w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                <Users className="w-6 h-6 text-white" />
              </div>
              <h3 className="font-bold text-[#4D4C4C] mb-2">Actividades</h3>
              <p className="text-sm text-[#7C8788]">
                Orientación personalizada, formación profesional, inserción laboral y seguimiento continuo.
              </p>
            </div>

            <div className="bg-[#8CC163]/5 p-6 rounded-xl">
              <div className="bg-[#8CC163] w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                <TrendingUp className="w-6 h-6 text-white" />
              </div>
              <h3 className="font-bold text-[#4D4C4C] mb-2">Impacto</h3>
              <p className="text-sm text-[#7C8788]">
                Inclusión social efectiva, autonomía personal y acceso real al mercado laboral.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Digital Platform */}
      <section id="plataforma" className="py-20 bg-gradient-to-br from-[#379AC4]/5 to-[#1282A8]/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12 items-center mb-16">
            <div>
              <img src={appWecoopLogo} alt="APP WECOOP" className="h-20 mb-6" />
              <h2 className="text-4xl font-bold text-[#4D4C4C] mb-6">Plataforma Digital</h2>
              <p className="text-xl text-[#7C8788] mb-8">
                Desarrollada en colaboración con nuestro partner tecnológico AYNIX, la plataforma APP WECOOP democratiza el acceso a servicios y oportunidades a través de una solución digital intuitiva y accesible.
              </p>

              <div className="space-y-4">
                {[
                  { icon: UserCheck, text: 'Registro y perfil personalizado' },
                  { icon: Calendar, text: 'Reserva de citas con operadores' },
                  { icon: GraduationCap, text: 'Acceso a formación y recursos' },
                  { icon: Smartphone, text: 'Comunicación directa y seguimiento' }
                ].map((item, index) => (
                  <div key={index} className="flex items-center gap-3">
                    <div className="bg-[#1282A8] w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                      <item.icon className="w-5 h-5 text-white" />
                    </div>
                    <span className="text-[#4D4C4C]">{item.text}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <img src={mobileAppImage} alt="App móvil" className="rounded-xl shadow-lg" />
              <img src={platformImage} alt="Plataforma digital" className="rounded-xl shadow-lg" />
              <img src={socialWorkerImage} alt="Gestión de servicios" className="rounded-xl shadow-lg col-span-2" />
            </div>
          </div>

          <div className="bg-white p-8 rounded-2xl shadow-xl">
            <h3 className="text-2xl font-bold text-[#4D4C4C] mb-8 text-center">Cómo funciona el sistema</h3>
            <div className="grid md:grid-cols-6 gap-6 relative">
              {[
                { icon: UserPlus, label: 'Registro', description: 'Crea tu perfil', color: '#1282A8' },
                { icon: LogIn, label: 'Acceso a servicios', description: 'Explora recursos', color: '#379AC4' },
                { icon: Calendar, label: 'Reserva de citas', description: 'Agenda reuniones', color: '#59B575' },
                { icon: GraduationCap, label: 'Formación', description: 'Desarrolla habilidades', color: '#8CC163' },
                { icon: MessageCircle, label: 'Comunicación', description: 'Contacta operadores', color: '#EC6B5C' },
                { icon: ClipboardList, label: 'Seguimiento', description: 'Monitorea progreso', color: '#E6266B' }
              ].map((item, index) => (
                <div key={index} className="relative">
                  <div className="text-center">
                    <div
                      className="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg hover:scale-110 transition-transform duration-300"
                      style={{ backgroundColor: item.color }}
                    >
                      <item.icon className="w-10 h-10 text-white" strokeWidth={2} />
                    </div>
                    <h4 className="text-sm font-bold text-[#4D4C4C] mb-1">{item.label}</h4>
                    <p className="text-xs text-[#7C8788]">{item.description}</p>
                  </div>
                  {index < 5 && (
                    <div className="hidden md:block absolute top-10 -right-3 z-10">
                      <ArrowRight className="w-6 h-6 text-[#7C8788]/30" strokeWidth={2.5} />
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Social Impact */}
      <section id="impacto" className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-[#4D4C4C] mb-4">Nuestro Impacto Social</h2>
            <p className="text-xl text-[#7C8788] max-w-3xl mx-auto">
              Resultados medibles de un modelo que transforma vidas y genera oportunidades reales.
            </p>
          </div>

          <div className="grid md:grid-cols-4 gap-8">
            {[
              { value: '400+', label: 'Beneficiarios', icon: Users, color: '#1282A8' },
              { value: '150+', label: 'Recorridos Formativos', icon: GraduationCap, color: '#59B575' },
              { value: '80+', label: 'Inserciones Laborales', icon: Briefcase, color: '#E6266B' },
              { value: '300+', label: 'Usuarios Plataforma', icon: Smartphone, color: '#379AC4' }
            ].map((stat, index) => (
              <div key={index} className="bg-gradient-to-br from-gray-50 to-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition text-center">
                <div
                  className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                  style={{ backgroundColor: `${stat.color}20` }}
                >
                  <stat.icon className="w-8 h-8" style={{ color: stat.color }} />
                </div>
                <div className="text-4xl font-bold mb-2" style={{ color: stat.color }}>
                  {stat.value}
                </div>
                <div className="text-[#7C8788]">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Partners */}
      <section id="partners" className="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-[#4D4C4C] mb-4">Red de Partners</h2>
            <p className="text-xl text-[#7C8788] max-w-3xl mx-auto">
              Colaboramos con instituciones, organizaciones y empresas comprometidas con la inclusión social.
            </p>
          </div>

          <div className="bg-white p-12 rounded-2xl shadow-lg">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 items-center">
              <div className="flex justify-center">
                <div className="text-center">
                  <div className="text-lg font-bold text-[#4D4C4C]">Partner Tecnológico</div>
                  <img src={aynixLogo} alt="AYNIX" className="h-12 mt-2" />
                </div>
              </div>
              {[1, 2, 3, 4, 5, 6].map((i) => (
                <div key={i} className="bg-gray-100 h-24 rounded-lg flex items-center justify-center">
                  <Building2 className="w-12 h-12 text-gray-400" />
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Collaborate */}
      <section className="py-20 bg-gradient-to-br from-[#1282A8] to-[#379AC4] text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-4xl font-bold mb-6">Colabora con WECOOP</h2>
              <p className="text-xl mb-8 text-white/90">
                Únete a nuestra red de partners y contribuye a crear oportunidades de inclusión social y laboral.
              </p>

              <div className="grid grid-cols-2 gap-4">
                <div className="bg-white/10 backdrop-blur-sm p-6 rounded-xl">
                  <Building2 className="w-8 h-8 mb-3" />
                  <h3 className="font-bold mb-2">Instituciones Públicas</h3>
                  <p className="text-sm text-white/80">Alianzas estratégicas para ampliar el impacto</p>
                </div>
                <div className="bg-white/10 backdrop-blur-sm p-6 rounded-xl">
                  <Briefcase className="w-8 h-8 mb-3" />
                  <h3 className="font-bold mb-2">Empresas</h3>
                  <p className="text-sm text-white/80">Oportunidades de empleo y RSC</p>
                </div>
                <div className="bg-white/10 backdrop-blur-sm p-6 rounded-xl">
                  <Heart className="w-8 h-8 mb-3" />
                  <h3 className="font-bold mb-2">Fundaciones</h3>
                  <p className="text-sm text-white/80">Apoyo a proyectos de inclusión</p>
                </div>
                <div className="bg-white/10 backdrop-blur-sm p-6 rounded-xl">
                  <HandshakeIcon className="w-8 h-8 mb-3" />
                  <h3 className="font-bold mb-2">Voluntarios</h3>
                  <p className="text-sm text-white/80">Comparte tu tiempo y talento</p>
                </div>
              </div>

              <button className="mt-8 bg-white text-[#1282A8] px-8 py-4 rounded-lg hover:bg-gray-100 transition font-bold">
                Quiero colaborar
              </button>
            </div>

            <div className="relative">
              <img src={newCollaborationImage} alt="Colaboración" className="rounded-2xl shadow-2xl mx-[0px] mt-[0px] mb-[-77px]" />
            </div>
          </div>
        </div>
      </section>

      {/* Contact */}
      <section id="contacto" className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-12">
            <div>
              <h2 className="text-4xl font-bold text-[#4D4C4C] mb-6">Contáctanos</h2>
              <p className="text-xl text-[#7C8788] mb-8">
                Estamos aquí para escucharte. Contáctanos para más información sobre nuestros servicios o para explorar oportunidades de colaboración.
              </p>

              <div className="space-y-6">
                <div className="flex items-start gap-4">
                  <div className="bg-[#1282A8]/10 w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0">
                    <MapPin className="w-6 h-6 text-[#1282A8]" />
                  </div>
                  <div>
                    <div className="font-bold text-[#4D4C4C] mb-1">Dirección</div>
                    <div className="text-[#7C8788]">Via Populonia 8<br />Milano, Italia</div>
                  </div>
                </div>

                <div className="flex items-start gap-4">
                  <div className="bg-[#59B575]/10 w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0">
                    <Mail className="w-6 h-6 text-[#59B575]" />
                  </div>
                  <div>
                    <div className="font-bold text-[#4D4C4C] mb-1">Email</div>
                    <div className="text-[#7C8788]">info@wecoop.org</div>
                  </div>
                </div>

                <div className="flex items-start gap-4">
                  <div className="bg-[#E6266B]/10 w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0">
                    <Phone className="w-6 h-6 text-[#E6266B]" />
                  </div>
                  <div>
                    <div className="font-bold text-[#4D4C4C] mb-1">Teléfono</div>
                    <div className="text-[#7C8788]">+39 02 XXXX XXXX</div>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-gray-50 p-8 rounded-2xl">
              <form className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-[#4D4C4C] mb-2">Nombre</label>
                  <input
                    type="text"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1282A8]"
                    placeholder="Tu nombre"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#4D4C4C] mb-2">Email</label>
                  <input
                    type="email"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1282A8]"
                    placeholder="tu@email.com"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#4D4C4C] mb-2">Mensaje</label>
                  <textarea
                    rows={4}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1282A8]"
                    placeholder="¿En qué podemos ayudarte?"
                  />
                </div>
                <button
                  type="submit"
                  className="w-full bg-[#1282A8] text-white px-6 py-3 rounded-lg hover:bg-[#379AC4] transition"
                >
                  Enviar mensaje
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-[#4D4C4C] text-white py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-4 gap-8 mb-8">
            <div>
              <img src={wecoopLogo} alt="WECOOP" className="h-12 mb-4 brightness-0 invert" />
              <p className="text-gray-400 text-sm">
                Un ecosistema de inclusión y oportunidades para todos.
              </p>
            </div>
            <div>
              <h4 className="font-bold mb-4">WECOOP</h4>
              <ul className="space-y-2 text-sm text-gray-400">
                <li><a href="#que-es" className="hover:text-white transition">Qué es WECOOP</a></li>
                <li><a href="#passaparola" className="hover:text-white transition">Passaparola</a></li>
                <li><a href="#plataforma" className="hover:text-white transition">Plataforma Digital</a></li>
                <li><a href="#impacto" className="hover:text-white transition">Impacto</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-bold mb-4">Colabora</h4>
              <ul className="space-y-2 text-sm text-gray-400">
                <li><a href="#" className="hover:text-white transition">Empresas</a></li>
                <li><a href="#" className="hover:text-white transition">Instituciones</a></li>
                <li><a href="#" className="hover:text-white transition">Fundaciones</a></li>
                <li><a href="#" className="hover:text-white transition">Voluntarios</a></li>
              </ul>
            </div>
            <div>
              <h4 className="font-bold mb-4">Contacto</h4>
              <ul className="space-y-2 text-sm text-gray-400">
                <li>Via Populonia 8, Milano</li>
                <li>info@wecoop.org</li>
                <li>+39 351 511 2113</li>
              </ul>
            </div>
          </div>
          <div className="border-t border-gray-700 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p className="text-sm text-gray-400">© 2026 WECOOP. Todos los derechos reservados.</p>
            <div className="flex gap-4 mt-4 md:mt-0">
              <img src={passaparolaLogo} alt="Passaparola" className="h-16 brightness-0 invert" />
              <img src={appWecoopLogo} alt="APP WECOOP" className="h-10 opacity-70" />
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
